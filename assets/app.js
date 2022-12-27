import './styles/app.css';
import './bootstrap';
import dayjs from 'dayjs';
import AdvancedFormat from 'dayjs/plugin/advancedFormat';
import UTC from 'dayjs/plugin/utc';

dayjs.extend(AdvancedFormat);
dayjs.extend(UTC);