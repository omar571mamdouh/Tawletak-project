import './bootstrap';
import { initFirebaseMessaging } from "./firebase-messaging";  //  صح

document.addEventListener("DOMContentLoaded", async () => {
    // Initialize FCM
    await initFirebaseMessaging();
});