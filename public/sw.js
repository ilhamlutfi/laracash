importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyC7eS_NEip3Jhr1MhuiNITMaV2U9ceKwPY",
    authDomain: "cashapp-pwa.firebaseapp.com",
    projectId: "cashapp-pwa",
    storageBucket: "cashapp-pwa.firebasestorage.app",
    messagingSenderId: "1024421524490",
    appId: "1:1024421524490:web:727b8eac441cd933049bf3",
    measurementId: "G-3G4PXBDTP5"
});

const messaging = firebase.messaging();

// === PERBAIKAN: WAJIB ADA AGAR NOTIFIKASI MUNCUL SAAT APLIKASI DITUTUP ===
messaging.onBackgroundMessage((payload) => {
    console.log('[sw.js] Menerima notifikasi di background: ', payload);

    // Membaca fallback data payload dari REST API V1 Laravel
    const title = payload.notification?.title || payload.data?.title || "[CASHAPP] Notifikasi";
    const body = payload.notification?.body || payload.data?.body || "Ada transaksi baru.";
    const targetUrl = payload.data?.url || '/';

    const notificationOptions = {
        body: body,
        icon: '/img/logo-shortcut.png', // Pastikan file ikon ini ada di folder public/
        badge: '/img/logo-shortcut.png', // Ikon kecil status bar Android (bisa gunakan icon-192 jika belum punya)
        data: {
            url: targetUrl
        }
    };

    return self.registration.showNotification(title, notificationOptions);
});

// Jika notifikasi diklik, buka atau fokuskan ke aplikasi CashApp
self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    let targetUrl = '/';
    if (event.notification.data && event.notification.data.url) {
        targetUrl = event.notification.data.url;
    }

    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then((windowClients) => {
            // Jika PWA sudah terbuka, langsung fokuskan ke tab tersebut
            for (let i = 0; i < windowClients.length; i++) {
                let client = windowClients[i];
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            // Jika belum terbuka, buka window baru
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
