// Bind a proxy variable to the change event of a selector
function bind(proxy, selector, eventType = "input") {
    document.addEventListener(
        eventType,
        function (event) {
            if (!event.target.matches(selector)) return;
            proxy.value = event.target.value;
        }
    );
}

function bindForm(selector, handler, preventDefault = true) {
    document.addEventListener(
        "submit",
        function (event) {
            if (!event.target.matches(selector)) return;
            event.preventDefault(true === preventDefault ? true : false);
            handler(event.target);
        }
    )
}

export { bind, bindForm };
