import { initializeApp } from "firebase/app";
import { getMessaging, getToken, onMessage } from "firebase/messaging";


console.log("🔥 firebase.js loaded inside Filament");
window.__FIREBASE_LOADED__ = true;


/**
 * Firebase Web Config
 */
const firebaseConfig = {
    apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
    messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
    appId: import.meta.env.VITE_FIREBASE_APP_ID,
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

// Store current token
let currentFcmToken = null;

/**
 * Initialize Firebase Cloud Messaging
 * @returns {Promise<string|null>} FCM token or null
 */
export async function initFirebaseMessaging() {
    // Check browser support
    if (!("serviceWorker" in navigator)) {
        console.warn("Service workers are not supported in this browser.");
        return null;
    }

    if (!("Notification" in window)) {
        console.warn("Notifications are not supported in this browser.");
        return null;
    }

    try {
        // 1️⃣ Register service worker
        const registration = await navigator.serviceWorker.register(
            "/firebase-messaging-sw.js",
            { scope: "/" }
        );

        console.log("✅ Service Worker registered successfully");

        // Wait for service worker to be ready
        await navigator.serviceWorker.ready;

        // 2️⃣ Request notification permission
        const permission = await Notification.requestPermission();
        
        if (permission !== "granted") {
            console.warn("⚠️ Notification permission denied");
            return null;
        }

        console.log("✅ Notification permission granted");

        // 3️⃣ Get FCM token
        const token = await getToken(messaging, {
            vapidKey: import.meta.env.VITE_FIREBASE_VAPID_KEY,
            serviceWorkerRegistration: registration,
        });

        if (!token) {
            console.warn("⚠️ No FCM token returned");
            return null;
        }

        console.log("✅ FCM Token generated:", token);
        currentFcmToken = token;

        // 4️⃣ Send token to backend
        const sent = await sendTokenToBackend(token);
        
        if (sent) {
            console.log("✅ FCM token sent to backend successfully");
        }

        // 5️⃣ Handle foreground messages
        setupForegroundMessaging();

        return token;

    } catch (error) {
        console.error("❌ FCM initialization error:", error);
        return null;
    }
}

/**
 * Send FCM token to backend
 * @param {string} token - FCM token
 * @returns {Promise<boolean>} Success status
 */
async function sendTokenToBackend(token) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        if (!csrfToken) {
            console.warn("⚠️ CSRF token not found. Make sure meta csrf-token exists.");
        }

        const response = await fetch("/device-token", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest",
                ...(csrfToken ? { "X-CSRF-TOKEN": csrfToken } : {}),
            },
            credentials: "same-origin", // ✅ مهم للسيشن
            body: JSON.stringify({
                token: token,
                platform: "web",
            }),
        });

        if (!response.ok) {
            const errorText = await response.text().catch(() => "");
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }

        const data = await response.json().catch(() => ({}));
        console.log("✅ Backend response:", data);

        return true;

    } catch (error) {
        console.error("❌ Error sending token to backend:", error);
        return false;
    }
}


/**
 * Setup foreground message handling
 */
function setupForegroundMessaging() {
    onMessage(messaging, (payload) => {
        console.log("📨 Foreground message received:", payload);

        const title = payload.notification?.title || "إشعار جديد";
        const body = payload.notification?.body || "";
        const icon = payload.notification?.icon || "/logo.png";
        const data = payload.data || {};

        // Show browser notification
        if (Notification.permission === "granted") {
            const notification = new Notification(title, {
                body: body,
                icon: icon,
                badge: "/badge.png",
                tag: data.notification_id || Date.now().toString(),
                requireInteraction: false,
                data: data,
            });

            // Handle notification click
            notification.onclick = function(event) {
                event.preventDefault();
                window.focus();
                
                // Navigate to URL if provided
                if (data.url) {
                    window.location.href = data.url;
                }
                
                notification.close();
            };
        }

        // Dispatch custom event for UI updates
        window.dispatchEvent(new CustomEvent("fcm-notification", {
            detail: payload
        }));

        // Play notification sound (optional)
        playNotificationSound();

        // Show toast notification (if you have a toast library)
        showToastNotification(title, body);
    });
}

/**
 * Remove FCM token on logout
 * @returns {Promise<boolean>} Success status
 */
export async function removeFirebaseToken() {
    if (!currentFcmToken) {
        console.warn("⚠️ No FCM token to remove");
        return true;
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch("/device-token", {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest",
                ...(csrfToken ? { "X-CSRF-TOKEN": csrfToken } : {}),
            },
            credentials: "same-origin",
            body: JSON.stringify({
                token: currentFcmToken,
            }),
        });

        if (!response.ok) {
            const errorText = await response.text().catch(() => "");
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }

        console.log("✅ FCM token removed from backend");
        currentFcmToken = null;
        return true;

    } catch (error) {
        console.error("❌ Error removing FCM token:", error);
        return false;
    }
}


/**
 * Get current FCM token
 * @returns {string|null}
 */
export function getCurrentFcmToken() {
    return currentFcmToken;
}

/**
 * Play notification sound
 */
function playNotificationSound() {
    try {
        const audio = new Audio("/notification.mp3");
        audio.volume = 0.5;
        audio.play().catch(err => {
            // Silently fail if autoplay is blocked
            console.log("Could not play notification sound:", err.message);
        });
    } catch (error) {
        console.log("Notification sound error:", error);
    }
}

/**
 * Show toast notification
 * استبدل دي بالـ toast library اللي بتستخدمها
 * مثلاً: vue-toastification, react-hot-toast, etc.
 */
function showToastNotification(title, body) {
    // Example for vue-toastification
    // if (window.$toast) {
    //     window.$toast.info(body, { title: title });
    // }
    
    // Example for plain JavaScript (fallback)
    console.log(`🔔 ${title}: ${body}`);
    
    // TODO: Replace with your toast notification library
}

initFirebaseMessaging?.().then(t => console.log("🎯 token:", t));