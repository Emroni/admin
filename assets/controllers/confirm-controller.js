import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    connect() {
        const button = this.element.children[0];
        button.addEventListener('click', this.handleClick);
        button.addEventListener('auxclick', this.handleClick);
    }

    handleClick = (e) => {
        const text = e.currentTarget.getAttribute('data-confirm-text-value');
        if (!window.confirm(text)) {
            e.preventDefault();
        }
    };

}
