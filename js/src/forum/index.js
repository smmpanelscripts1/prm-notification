import app from 'flarum/forum/app';
import { extend, override } from 'flarum/common/extend';
import HeaderSecondary from 'flarum/forum/components/HeaderSecondary';
import NotificationsDropdown from 'flarum/forum/components/NotificationsDropdown';
import NotificationList from 'flarum/forum/components/NotificationList';
import NotificationListState from 'flarum/forum/states/NotificationListState';
import CampaignGroup from './components/CampaignGroup';
import GuestNotificationsDropdown from './components/GuestNotificationsDropdown';
import campaignState from './states/campaignState';

export { default as extend } from './extend';

app.initializers.add('prm-notification', () => {
  extend(app, 'mount', () => {
    if (app.session.user || app.forum.attribute('prmNotificationShowForGuests')) {
      campaignState.load();
    }
  });

  // Guests: core has no bell, so add one that only lists announcements.
  extend(HeaderSecondary.prototype, 'items', function (items) {
    if (app.session.user || !campaignState.count()) return;

    items.add('notifications', <GuestNotificationsDropdown state={null} />, 10);
  });

  // Members: announcements count towards the bell badge…
  override(NotificationsDropdown.prototype, 'getUnreadCount', function (original) {
    return (original() || 0) + campaignState.unreadCount();
  });

  override(NotificationsDropdown.prototype, 'getNewCount', function (original) {
    return (original() || 0) + campaignState.unreadCount();
  });

  extend(NotificationListState.prototype, 'markAllAsRead', () => campaignState.markRead());

  extend(NotificationsDropdown.prototype, 'onclick', () => campaignState.load());

  // …and are listed at the top of the normal notifications list.
  extend(NotificationList.prototype, 'view', function (vnode) {
    if (!campaignState.count() || !vnode || !vnode.attrs) return;

    vnode.attrs.hasItems = true;
    vnode.attrs.loading = false;
    vnode.children = [<CampaignGroup />].concat(vnode.children || []);
  });
});
