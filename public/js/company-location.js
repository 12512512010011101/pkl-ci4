/**
 * Halaman pembimbing: menentukan dan mengunci lokasi tempat PKL di peta (Leaflet + OpenStreetMap).
 * Titik bisa ditentukan dengan klik peta, geser pin, cari alamat, atau "Pakai lokasi saya".
 */
(function () {
  'use strict';

  var mapEl = document.getElementById('loc-map');
  if (!mapEl || typeof L === 'undefined') { return; }

  var latIn = document.getElementById('latitude');
  var lngIn = document.getElementById('longitude');
  var radIn = document.getElementById('radius_m');
  var msg = document.getElementById('loc-msg');
  var searchIn = document.getElementById('loc-search');
  var DEFAULT_CENTER = [-7.8480, 112.0178]; // Kediri

  function num(v) {
    var n = parseFloat(String(v).replace(',', '.'));
    return isNaN(n) ? null : n;
  }

  function say(text, type) {
    msg.className = 'small mt-2 text-' + (type || 'muted');
    msg.textContent = text;
  }

  function radius() {
    var r = num(radIn.value);
    return r && r > 0 ? r : 100;
  }

  var lat = num(latIn.value);
  var lng = num(lngIn.value);
  var hasPoint = lat !== null && lng !== null;

  var map = L.map(mapEl).setView(hasPoint ? [lat, lng] : DEFAULT_CENTER, hasPoint ? 17 : 13);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  var marker = null;
  var circle = null;

  function setPoint(la, ln, zoom) {
    la = Math.round(la * 1e7) / 1e7;
    ln = Math.round(ln * 1e7) / 1e7;
    latIn.value = la.toFixed(7);
    lngIn.value = ln.toFixed(7);

    if (!marker) {
      marker = L.marker([la, ln], { draggable: true }).addTo(map);
      marker.on('dragend', function () {
        var p = marker.getLatLng();
        setPoint(p.lat, p.lng);
      });
      circle = L.circle([la, ln], { radius: radius() }).addTo(map);
    } else {
      marker.setLatLng([la, ln]);
      circle.setLatLng([la, ln]);
    }
    circle.setRadius(radius());
    if (zoom) { map.setView([la, ln], zoom); }
  }

  if (hasPoint) { setPoint(lat, lng); }

  map.on('click', function (e) { setPoint(e.latlng.lat, e.latlng.lng); });

  radIn.addEventListener('input', function () { if (circle) { circle.setRadius(radius()); } });

  function fromInputs() {
    var la = num(latIn.value);
    var ln = num(lngIn.value);
    if (la !== null && ln !== null && la >= -90 && la <= 90 && ln >= -180 && ln <= 180) {
      setPoint(la, ln, 17);
    }
  }
  latIn.addEventListener('change', fromInputs);
  lngIn.addEventListener('change', fromInputs);

  // --- Pakai lokasi perangkat ini (pembimbing berada di tempat PKL) ---
  document.getElementById('btn-me').addEventListener('click', function () {
    if (!('geolocation' in navigator)) { say('Browser tidak mendukung pembacaan lokasi.', 'danger'); return; }
    if (!window.isSecureContext) { say('Lokasi hanya bisa dibaca lewat HTTPS atau http://localhost.', 'danger'); return; }
    say('Mengambil lokasi perangkat...', 'muted');
    navigator.geolocation.getCurrentPosition(function (pos) {
      setPoint(pos.coords.latitude, pos.coords.longitude, 18);
      say('Lokasi perangkat dipakai (akurasi \u00B1' + Math.round(pos.coords.accuracy) + ' m). Geser pin bila perlu.', 'success');
    }, function (err) {
      if (err.code === 1) { say('Izin lokasi ditolak atau lokasi perangkat mati. Nyalakan lokasi lalu izinkan di browser.', 'danger'); }
      else if (err.code === 2) { say('Lokasi tidak tersedia. Nyalakan GPS / Layanan Lokasi di perangkat.', 'danger'); }
      else { say('Pencarian lokasi terlalu lama. Coba lagi di area terbuka.', 'warning'); }
    }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
  });

  // --- Cari alamat (OpenStreetMap Nominatim) ---
  function search() {
    var q = searchIn.value.trim();
    if (!q) { return; }
    say('Mencari alamat...', 'muted');
    fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&countrycodes=id&q=' + encodeURIComponent(q), {
      headers: { 'Accept': 'application/json' }
    }).then(function (r) { return r.json(); }).then(function (list) {
      if (!list.length) {
        say('Alamat tidak ditemukan. Coba lebih singkat, atau klik langsung di peta.', 'warning');
        return;
      }
      setPoint(parseFloat(list[0].lat), parseFloat(list[0].lon), 18);
      say('Ditemukan: ' + list[0].display_name + '. Periksa dan geser pin bila perlu.', 'success');
    }).catch(function () {
      say('Gagal mencari alamat. Periksa koneksi internet.', 'danger');
    });
  }
  document.getElementById('btn-search').addEventListener('click', search);
  searchIn.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); search(); }
  });

  // Jangan kirim form kalau titik belum dipilih.
  document.getElementById('loc-form').addEventListener('submit', function (e) {
    if (num(latIn.value) === null || num(lngIn.value) === null) {
      e.preventDefault();
      say('Tentukan titik lokasi dulu: klik peta, cari alamat, atau pakai lokasi saya.', 'danger');
    }
  });
})();
