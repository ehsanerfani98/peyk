/**
 * Real-time Order Status Listener
 *
 * Usage:
 *   import { listenOrderStatus } from './listeners/orderStatusListener';
 *   const stop = listenOrderStatus(orderId, (payload) => {
 *       console.log('Order status changed:', payload.status);
 *   });
 *   // To stop listening: stop();
 */

/**
 * Listen for real-time order status changes on a private channel.
 *
 * @param {number} orderId - The order ID to listen for
 * @param {function} callback - Called with { order_id, status, timestamp }
 * @returns {function} A function to stop listening (unsubscribe)
 */
export function listenOrderStatus(orderId, callback) {
    const channel = window.Echo.private(`private-order.${orderId}`);

    channel.listen('.order.status-changed', (payload) => {
        callback(payload);
    });

    // Return unsubscribe function
    return () => {
        window.Echo.leave(`private-order.${orderId}`);
    };
}
