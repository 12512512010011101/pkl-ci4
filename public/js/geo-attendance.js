/**
 * Absensi berbasis lokasi (sisi siswa).
 *
 * - Membaca lokasi lewat Geolocation API browser (tidak butuh Node.js).
 * - Menampilkan status: lokasi aktif / mati / ditolak, plus jarak ke lokasi PKL.
 * - Memantau terus (watchPosition): kalau lokasi dimatikan, pemberitahuan muncul;
 *   kalau dinyalakan lagi, status pulih otomatis.
 * - Saat tombol absen ditekan, mengambil lokasi terbaru lalu mengirim lat/lng/accuracy
 *   bersama form. Keputusan diterima/ditolak tetap ada di server (Attendance.php).
 *
 * Catatan: Geolocation hanya jalan di HTTPS atau http://localhost.
 */
(function () {
  'use strict';

  var box = document.getElementById('geo-box');
  var statusEl = document.getElementById('geo-status');
  if (!box || !statusEl) { return; }

  var targetLat = parseFloat(box.dataset.lat);
  var targetLng = parseFloat(box.dataset.lng);
  var radius = parseInt(box.dataset.radius, 10) || 100;
  var hasTarget = !isNaN(targetLat) && !isNaN(targetLng);
  // Dibaca dari server (konstanta di Attendance.php lewat _geo.php), jadi tidak perlu diubah di sini.
  var MAX_ACCURACY = parseInt(box.dataset.maxAccuracy, 10) || 300;
  var enforce = box.dataset.enforce === '1'; // false = mode "kunci saja": jarak hanya dicatat

  var forms = document.querySelectorAll(
    'form[action$="attendance/checkin"], form[action$="attendance/checkout"]'
  );
  var watchId = null;

  var HOWTO =
    '<ul class="mb-0 mt-2">' +
    '<li>Nyalakan <b>Lokasi / GPS</b> di perangkat (Android: tarik layar dari atas lalu ketuk Lokasi; ' +
    'iPhone: Pengaturan &gt; Privasi &amp; Keamanan &gt; Layanan Lokasi).</li>' +
    '<li>Di browser, ketuk ikon gembok di samping alamat situs &gt; Izin &gt; Lokasi &gt; <b>Izinkan</b>.</li>' +
    '<li>Lalu klik <b>Coba lagi</b> atau muat ulang halaman.</li>' +
    '</ul>';

  function haversine(lat1, lng1, lat2, lng2) {
    var R = 6371000;
    var toRad = function (d) { return d * Math.PI / 180; };
    var dLat = toRad(lat2 - lat1);
    var dLng = toRad(lng2 - lng1);
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
    return 2 * R * Math.asin(Math.min(1, Math.sqrt(a)));
  }

  function render(type, html, withRetry) {
    statusEl.className = 'alert alert-' + type + ' mb-0';
    statusEl.innerHTML = html;
    if (withRetry) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'btn btn-sm btn-outline-dark mt-2';
      b.textContent = 'Coba lagi';
      b.addEventListener('click', start);
      statusEl.appendChild(b);
    }
  }

  function showError(err) {
    if (err && err.code === 1) {
      render('danger', '<strong>Lokasi tidak bisa dibaca.</strong> Izin lokasi ditolak atau Layanan Lokasi perangkat sedang mati. ' +
        'Absen tidak bisa dilakukan sebelum lokasi dinyalakan.' + HOWTO, true);
    } else if (err && err.code === 2) {
      render('danger', '<strong>Lokasi tidak tersedia.</strong> Biasanya GPS / Layanan Lokasi perangkat sedang mati. ' +
        'Nyalakan lokasi untuk bisa absen.' + HOWTO, true);
    } else if (err && err.code === 3) {
      render('warning', '<strong>Pencarian lokasi terlalu lama.</strong> Pindah ke area terbuka atau nyalakan GPS, lalu klik Coba lagi.', true);
    } else {
      render('danger', '<strong>Terjadi kesalahan saat membaca lokasi.</strong>', true);
    }
  }

  function onPosition(pos) {
    var acc = Math.round(pos.coords.accuracy);
    var lowAcc = acc > MAX_ACCURACY;
    var accNote = lowAcc
      ? ' <b>Akurasi rendah (\u00B1' + acc + ' m)</b>: ' + (enforce
          ? 'nyalakan GPS atau pindah ke area terbuka, jika tidak absen akan ditolak.'
          : 'lokasi tetap dicatat, tapi jaraknya kurang tepat (biasanya karena memakai laptop tanpa GPS).')
      : ' Akurasi \u00B1' + acc + ' m.';

    if (!hasTarget) {
      render('info', '<strong>Lokasi aktif.</strong>' + accNote +
        ' Pembimbing belum mengunci lokasi tempat PKL kamu, jadi jarak belum diperiksa.');
      return;
    }

    var d = Math.round(haversine(pos.coords.latitude, pos.coords.longitude, targetLat, targetLng));
    if (d <= radius) {
      render(lowAcc ? 'warning' : 'success',
        '<strong>Lokasi aktif.</strong> Kamu berada ' + d + ' m dari lokasi PKL (radius ' + radius + ' m).' + accNote);
    } else if (enforce) {
      render('warning',
        '<strong>Kamu di luar area PKL.</strong> Jarak ke lokasi PKL ' + d + ' m, batas absen ' + radius + ' m.' + accNote);
    } else {
      render('warning',
        '<strong>Kamu di luar area PKL.</strong> Jarak ke lokasi PKL ' + d + ' m, radius ' + radius + ' m. ' +
        'Saat ini absen tetap diterima, tapi jarakmu dicatat dan bisa dilihat pembimbing.' + accNote);
    }
  }

  function start() {
    if (!('geolocation' in navigator)) {
      render('danger', '<strong>Browser ini tidak mendukung pembacaan lokasi.</strong> Gunakan Chrome atau Safari versi terbaru di HP.');
      return;
    }
    if (!window.isSecureContext) {
      render('danger', '<strong>Halaman ini belum aman (bukan HTTPS).</strong> Browser memblokir lokasi di halaman seperti ini. ' +
        'Buka lewat alamat https:// atau http://localhost.');
      return;
    }
    render('secondary', 'Memeriksa lokasi perangkat...');
    if (watchId !== null) { navigator.geolocation.clearWatch(watchId); }
    watchId = navigator.geolocation.watchPosition(onPosition, showError, {
      enableHighAccuracy: true,
      maximumAge: 5000,
      timeout: 20000
    });
  }

  function setHidden(form, name, value) {
    var input = form.querySelector('input[name="' + name + '"]');
    if (!input) {
      input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      form.appendChild(input);
    }
    input.value = value;
  }

  Array.prototype.forEach.call(forms, function (form) {
    form.addEventListener('submit', function (e) {
      if (form.dataset.geoReady === '1') { return; } // lokasi sudah dilampirkan, kirim

      e.preventDefault();
      if (!('geolocation' in navigator) || !window.isSecureContext) {
        start();
        statusEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }

      var btn = form.querySelector('button');
      var label = btn ? btn.textContent : '';
      if (btn) { btn.disabled = true; btn.textContent = 'Mengambil lokasi...'; }

      navigator.geolocation.getCurrentPosition(function (pos) {
        setHidden(form, 'lat', pos.coords.latitude);
        setHidden(form, 'lng', pos.coords.longitude);
        setHidden(form, 'accuracy', pos.coords.accuracy);
        form.dataset.geoReady = '1';
        form.submit();
      }, function (err) {
        if (btn) { btn.disabled = false; btn.textContent = label; }
        showError(err);
        statusEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
      // maximumAge 15 dtk: memakai fix terbaru dari pemantauan yang sedang berjalan (cepat),
      // tapi tidak menerima lokasi yang sudah lama.
      }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 15000 });
    });
  });

  // Izin lokasi diubah di pengaturan browser: periksa ulang otomatis.
  if (navigator.permissions && navigator.permissions.query) {
    navigator.permissions.query({ name: 'geolocation' }).then(function (p) {
      p.onchange = start;
    }).catch(function () { /* browser lama: abaikan */ });
  }

  // Kembali ke tab setelah menyalakan GPS di pengaturan perangkat.
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) { start(); }
  });

  start();
})();
