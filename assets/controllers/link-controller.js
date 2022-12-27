import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    
    connect() {
        this.element.addEventListener('click', this.handleClick);
        this.element.addEventListener('auxclick', this.handleClick);
    }

    handleClick = (e) => {
        if (!e.target.getAttribute('href')) {
            const link = e.currentTarget.getAttribute('data-link-path-value');
            if (e.ctrlKey || e.metaKey || e.type === 'auxclick') {
                window.open(link);
            } else {
                window.location = link;
            }
        }
    }

}
