import app from 'flarum/forum/app';
import NotificationsDropdown from 'flarum/forum/components/NotificationsDropdown';
import HeaderList from 'flarum/forum/components/HeaderList';
import Button from 'flarum/common/components/Button';
import Tooltip from 'flarum/common/components/Tooltip';
import ItemList from 'flarum/common/utils/ItemList';
import CampaignGroup from './CampaignGroup';
import campaignState from '../states/campaignState';

/**
 * Core renders no notifications dropdown for guests; this one shows only site announcements.
 */
export default class GuestNotificationsDropdown extends NotificationsDropdown {
  getContent() {
    return (
      <HeaderList
        className="NotificationList"
        title={app.translator.trans('core.forum.notifications.title')}
        controls={this.controlItems()}
        hasItems={campaignState.count() > 0}
        loading={campaignState.loading && !campaignState.count()}
        emptyText={app.translator.trans('core.forum.notifications.empty_text')}
      >
        <CampaignGroup />
      </HeaderList>
    );
  }

  controlItems() {
    const items = new ItemList();

    items.add(
      'mark_all_as_read',
      <Tooltip text={app.translator.trans('core.forum.notifications.mark_all_as_read_tooltip')}>
        <Button
          className="Button Button--link"
          data-container=".NotificationList"
          icon="fas fa-check"
          title={app.translator.trans('core.forum.notifications.mark_all_as_read_tooltip')}
          onclick={() => campaignState.markRead()}
        />
      </Tooltip>,
      70
    );

    return items;
  }

  goToRoute() {}

  onclick() {
    campaignState.load();
  }

  getUnreadCount() {
    return campaignState.unreadCount();
  }

  getNewCount() {
    return campaignState.unreadCount();
  }
}
