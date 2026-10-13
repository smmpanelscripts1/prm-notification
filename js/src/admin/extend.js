import app from 'flarum/admin/app';
import Extend from 'flarum/common/extenders';
import CampaignsPage from './components/CampaignsPage';
import Campaign from './models/Campaign';

export default [
  new Extend.Store().add('prm-notification-campaigns', Campaign),

  new Extend.Admin()
    .permission(
      () => ({
        icon: 'fas fa-bullhorn',
        label: app.translator.trans('prm-notification.admin.permissions.manage_label'),
        permission: 'prm-notification.manage',
      }),
      'moderate'
    )
    .page(CampaignsPage),
];
