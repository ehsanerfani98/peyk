/**
 * Real-time Courier Location Tracker
 *
 * Usage:
 *   import { trackCourierLocation } from './listeners/courierLocationListener';
 *   const stop = trackCourierLocation(courierId, (payload) => {
 *       updateMapMarker(payload.lat, payload.lng);
 *   });
 *   // To stop tracking: stop();
 */

/**
 * Track courier's real-time location via a presence channel.
 *
 * @param {number} courierId - The courier ID to track
 * @param {function} callback - Called with { courier_id, lat, lng, timestamp }
 * @returns {function} A function to stop tracking (unsubscribe)
 */
export function trackCourierLocation(courierId, callback) {
    const channel = window.Echo.join(`presence-courier.${courierId}`)
        .here((users) => {
            console.log(`[CourierLocation] Users in channel: ${users.length}`, users);
        })
        .joining((user) => {
            console.log(`[CourierLocation] User joined: ${user.name} (${user.role})`);
        })
        .leaving((user) => {
            console.log(`[CourierLocation] User left: ${user.name} (${user.role})`);
        });

    channel.listen('.courier.location-updated', (payload) => {
        callback(payload);
    });

    // Return unsubscribe function
    return () => {
        window.Echo.leave(`presence-courier.${courierId}`);
    };
}
