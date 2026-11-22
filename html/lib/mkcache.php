<?php
/*
 *  Cache sementara daftar catatan penjualan per bulan (owner) dari router.
 *  Mengurangi beban router, karena query script di RouterOS cukup berat.
 */
if (!function_exists('mk_sales_cache_file')) {
	function mk_sales_cache_file($session, $owner) {
		return sys_get_temp_dir() . '/mk_sales_' . md5($session . '|' . $owner) . '.json';
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
		$rows = $API->comm("/system/script/print", array("?owner" => "$owner"));
		if (!is_array($rows)) {
			return array();
		}
		@file_put_contents($file, json_encode($rows));
		return $rows;
	}
}
if (!function_exists('mk_sales_clear')) {
	function mk_sales_clear($session, $owner) {
		@unlink(mk_sales_cache_file($session, $owner));
	}
}
