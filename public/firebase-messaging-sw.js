importScripts("https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js");
importScripts("https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging-compat.js");

firebase.initializeApp({
  apiKey: "AIzaSyD60eseyaMdG8x9nbTJKiFeJN9Yl4c1DM",
  authDomain: "tawletak-6ac74.firebaseapp.com",
  projectId: "tawletak-6ac74",
  messagingSenderId: "151714360307",
  appId: "1:151714360307:web:58306799a3d4f0bda94086",
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
  const title = payload.notification?.title ?? "Notification";

  const options = {
    body: payload.notification?.body ?? "",
    icon: payload.notification?.icon ?? "/logo.png",
    badge: "/badge.png",
    data: payload.data ?? {},
  };

  self.registration.showNotification(title, options);
});



self.addEventListener("notificationclick", function (event) {
  event.notification.close();

  const url = event.notification?.data?.url || "/admin";

  event.waitUntil(
    clients.matchAll({ type: "window", includeUncontrolled: true }).then((clientList) => {
      // لو في تاب مفتوحة افتحها وركّز عليها
      for (const client of clientList) {
        if ("focus" in client) return client.focus();
      }
      // لو مفيش افتح جديدة
      if (clients.openWindow) return clients.openWindow(url);
    })
  );
});

