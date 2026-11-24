<?php
/*
 *  Cache sementara daftar catatan penjualan per bulan (owner) dari router.
 *  Mengurangi beban router, karena query script di RouterOS cukup berat.
 *
 *  Catatan: sebagian skrip penjualan lama memiliki owner "*sys" (karena variabel
 *  bulan/tahun tidak terselesaikan saat on-login). Skrip seperti itu tetap
 *  dimasukkan ke laporan bulanan dengan mencocokkan tanggal pada nama skrip.
 */
if (!function_exists('mk_sales_cache_file')) {
	function mk_sales_cache_file($session, $owner) {
		return sys_get_temp_dir() . '/mk_sales_' . md5($session . '|' . $owner) . '.json';
	}
}
if (!function_exists('mk_sales_month_parts')) {
	function mk_sales_month_parts($owner) {
		$mon = strtolower(substr((string)$owner, 0, 3));
		$yr = substr((string)$owner, 3);
		return array($mon, $yr);
	}
}
if (!function_exists('mk_sales_matches_month')) {
	function mk_sales_matches_month($row, $owner) {
		if (($row['owner'] ?? '') === $owner) {
			return true;
		}
		if (($row['owner'] ?? '') !== '*sys') {
			return false;
		}
		list($mon, $yr) = mk_sales_month_parts($owner);
		$datepart = explode("-|-", (string)($row['name'] ?? ''))[0];
		$parts = explode("/", $datepart);
		if (count($parts) < 3) {
			return false;
		}
		return strtolower(substr(trim($parts[0]), 0, 3)) === $mon && trim($parts[2]) === $yr;
	}
}
if (!function_exists('mk_sales_cached')) {
	function mk_sales_cached($API, $session, $owner, $ttl = 60) {
		$file = mk_sales_cache_file($session, $owner);
		if (is_file($file) && (time() - filemtime($file)) < $ttl) {
			$data = json_decode(file_get_contents($file), true);
			if (is_array($data)) {
				return $data;
			}
		}
		$rows = $API->comm("/system/script/print", array("?comment" => "mikhmon"));
		if (!is_array($rows)) {
			$rows = array();
		}
		$filtered = array();
		foreach ($rows as $r) {
			if (mk_sales_matches_month($r, $owner)) {
				$filtered[] = $r;
			}
		}
		@file_put_contents($file, json_encode($filtered));
		return $filtered;
	}
}
if (!function_exists('mk_sales_clear')) {
	function mk_sales_clear($session, $owner) {
		@unlink(mk_sales_cache_file($session, $owner));
	}
}
if (!function_exists('mk_sales_filtered_all')) {
	function mk_sales_filtered_all($API, $owner) {
		$rows = $API->comm("/system/script/print", array("?comment" => "mikhmon"));
		if (!is_array($rows)) {
			return array();
		}
		$out = array();
		foreach ($rows as $r) {
			if (mk_sales_matches_month($r, $owner)) {
				$out[] = $r;
			}
		}
		return $out;
	}
}
?>