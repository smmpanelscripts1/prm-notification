import Extend from 'flarum/common/extenders';
import Campaign from './models/Campaign';

export default [
  new Extend.Store().add('prm-notification-campaigns', Campaign),
];
