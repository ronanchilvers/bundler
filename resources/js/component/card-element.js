import { signal, component } from '/js/vendor/reef.es.min.js';

class CardElement extends HTMLElement
{
    static get observedAttributes() {
        return [
            'id',
        ];
    }

    constructor () {
        super();
        this._id = null;
        this.uuid = crypto.randomUUID();
        this.signal = signal({
            title: null,
        }, this.uuid);
        component(
            this,
            this.template.bind(this),
            {
                signals: [this.uuid],
            }
        )
    }

    setTitle(title) {
        // console.log('ColumnElement: setTitle(' + title + ')');
        this.signal.title = title;
    }

    attributeChangedCallback(name, oldValue, newValue) {
        if (name == "id") {
            this._id = newValue;
        }
    }

    template () {
        let { title } = this.signal;
        return `
        <div class="card" id="card${this._id}">
            <div class="card__header">
                <h3>${title}</h3>
            </div>
        </div>
        `;
    }
}

customElements.define(
    'card-element',
    CardElement
);
