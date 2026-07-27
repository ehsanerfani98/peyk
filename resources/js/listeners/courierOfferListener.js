/**
 * Real-time Courier Offer Listener
 *
 * Usage:
 *   import { listenCourierOffers } from './listeners/courierOfferListener';
 *   const stop = listenCourierOffers(courierId, (payload) => {
 *       if (payload.order_id === null) {
 *           // Offer was cancelled/timed out - dismiss the offer UI
 *           console.log('Offer expired or cancelled');
 *       } else {
 *           console.log('New order offer:', payload);
 *       }
 *   });
 *   // To stop listening: stop();
 */

/**
 * Listen for real-time courier order offers on a private channel.
 *
 * When `payload.order_id` is `null`, it means the offer has been cancelled
 * (e.g., timeout, system rejection) and the UI should dismiss the offer card.
 *
 * @param {number} courierId - The courier ID to listen for
 * @param {function} callback - Called with { order_id, pickup_address, delivery_address, price, distance_meters }
 *                              order_id will be null when the offer is cancelled/expired.
 * @returns {function} A function to stop listening (unsubscribe)
 */
export function listenCourierOffers(courierId, callback) {
    const channel = window.Echo.private(`private-courier.${courierId}`);

    channel.listen('.courier.offer-received', (payload) => {
        callback(payload);
    });

    // Return unsubscribe function
    return () => {
        window.Echo.leave(`private-courier.${courierId}`);
    };
}
