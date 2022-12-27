import { Controller } from '@hotwired/stimulus';
import dayjs from 'dayjs';

export default class extends Controller {
    
    static values = {
        now: String,
        start: String,
        title: String,
    };
    
    connect() {
        this.offset = this.getTime(this.startValue) + (this.getTime() - this.getTime(this.nowValue));

        setInterval(this.tick, 60000);
        this.tick();
    }

    getTime = (date) => {
        const value = dayjs.utc(date).format('x');
        return parseInt(value);
    }

    tick = () => {
        const time = dayjs.utc(this.getTime() - this.offset).format('H:mm');
        this.element.innerText = time;
        document.title = `${time} | ${this.titleValue} | Admin`;
    }

}
