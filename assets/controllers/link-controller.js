import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static values = {
        path: String,
    };

    connect() {
        this.element.addEventListener('click', this.handleClick);
        this.element.addEventListener('auxclick', this.handleClick);
    }

    handleClick = (e) => {
        if (!e.target.getAttribute('href')) {
            if (e.ctrlKey || e.metaKey || e.type === 'auxclick') {
                window.open(this.pathValue);
            } else {
                window.location = this.pathValue;
            }
        }
    };

}
