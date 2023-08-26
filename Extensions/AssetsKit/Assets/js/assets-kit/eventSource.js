/**
 * Event source was made to work with the EventSource component
 *
 * How to use it ?
 * ```js
 * eventSource("/my-url", {
 *     "my-event" : function(data) {
 *          console.log("I have received", data);
 *     }
 * })
 * ```
 */

declareNewBridge("eventSource", {

    new: function(url, handler, endEventName="event-source-end") {
        let instance = new EventSource(url);

        for (const [event, callback] of Object.entries(handler))
            instance.addEventListener(event, event => {
                callback(JSON.parse(event.data ?? '{}'), event);
            } );

        instance.addEventListener(endEventName, ()=>{
            instance.close();
        })
    }
}, module => {return {
    eventSource: module.new
}})