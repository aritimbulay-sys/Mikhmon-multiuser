<?php
/*
 *		Copyright (C) 2018 Laksamadi Guko.
 *
 *		This program is free software; you can redistribute it and/or modify
 *		it under the terms of the GNU General Public License as published by
 *		the Free Software Foundation; either version 2 of the License, or
 *		(at your option) any later version.
 *
 *		This program is distributed in the hope that it will be useful,
 *		but WITHOUT ANY WARRANTY; without even the implied warranty of
 *		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.		See the
 *		GNU General Public License for more details.
 *
 *		You should have received a copy of the GNU General Public License
 *		along with this program.		If not, see <http://www.gnu.org/licenses/>.
 */
session_start();
 // hide all error
error_reporting(0);
?>
<!DOCTYPE html>
<html>
	<head>
		<title>MIKHMON <?= $hotspotname; ?></title>
		<meta charset="utf-8">
		<meta http-equiv="cache-control" content="private" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<!-- Tell the browser to be responsive to screen width -->
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- Theme color -->
		<meta name="theme-color" content="<?= $themecolor ?>" />
		<!-- Font Awesome -->
		<link rel="stylesheet" type="text/css" href="css/font-awesome/css/font-awesome.min.css" />
		<!-- Mikhmon UI -->
		<link rel="stylesheet" href="css/mikhmon-ui.<?= $theme; ?>.min.css">
		<!-- Tema tambahan Mikhmon multi-user -->
		<link rel="stylesheet" href="css/mk-theme.css?v=31">
		<script>
		(function () {
			var key = 'mk-theme';
			function apply(v) { document.documentElement.setAttribute('data-mk', v); }
			function mark() {
				var cur = document.documentElement.getAttribute('data-mk');
				document.querySelectorAll('[data-mk-set]').forEach(function (b) {
					b.classList.toggle('active', b.getAttribute('data-mk-set') === cur);
				});
			}
			var saved = 'dark';
			try { saved = localStorage.getItem(key) || 'dark'; } catch (e) {}
			if (saved !== 'dark' && saved !== 'light') { saved = 'dark'; }
			apply(saved);
			var modes = { dark: ['fa-moon-o', 'Gelap'], light: ['fa-sun-o', 'Terang'] };
			var next = { dark: 'light', light: 'dark' };
			function cycleLabel() {
				var cur = document.documentElement.getAttribute('data-mk');
				document.querySelectorAll('[data-mk-cycle]').forEach(function (b) {
					var m = modes[cur] || modes.dark;
					b.querySelector('i').className = 'fa ' + m[0];
					b.querySelector('span').textContent = m[1];
				});
			}
			document.addEventListener('DOMContentLoaded', function () {
				document.querySelectorAll('[data-mk-set]').forEach(function (b) {
					b.addEventListener('click', function () {
						var v = b.getAttribute('data-mk-set');
						try { localStorage.setItem(key, v); } catch (e) {}
						apply(v);
						mark();
						cycleLabel();
					});
				});
				document.querySelectorAll('[data-mk-cycle]').forEach(function (b) {
					b.addEventListener('click', function () {
						var cur = document.documentElement.getAttribute('data-mk') || 'dark';
						var v = next[cur] || 'dark';
						try { localStorage.setItem(key, v); } catch (e) {}
						apply(v);
						mark();
						cycleLabel();
					});
				});
				mark();
				cycleLabel();
				var sn = document.getElementById('sidenav');
				if (sn) {
					sn.querySelectorAll('a').forEach(function (a) {
						if (!a.getAttribute('title')) { a.setAttribute('title', a.textContent.trim()); }
					});
					var syncNav = function () {
						var closed = sn.style.width ? parseFloat(sn.style.width) === 0 : parseFloat(getComputedStyle(sn).width) === 0;
						document.body.classList.toggle('mk-collapsed', closed);
					};
					syncNav();
					new MutationObserver(syncNav).observe(sn, { attributes: true, attributeFilter: ['style'] });
				}
			});
		})();
		</script>
		<!-- favicon -->
		<link rel="icon" type="image/svg+xml" href="./img/favicon.svg" />
		<!-- jQuery -->
		<script src="js/jquery.min.js"></script>
		<!-- pace -->
		<link href="css/pace.<?= $theme; ?>.css" rel="stylesheet" />
		<script src="js/pace.min.js"></script>

		
	</head>
	<body>
		<div class="wrapper">

			
