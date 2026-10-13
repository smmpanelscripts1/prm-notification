import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
import Button from 'flarum/common/components/Button';
import HeaderListGroup from 'flarum/forum/components/HeaderListGroup';
import HeaderListItem from 'flarum/forum/components/HeaderListItem';
import campaignState from '../states/campaignState';
import { t } from '../utils';

/**
 * Site announcements rendered as a group inside the notifications list.
 */
export default class CampaignGroup extends Component {
  view() {
    return (
      <HeaderListGroup label={t('group_label')}>
        {campaignState.items.map((item) => this.item(item))}
      </HeaderListGroup>
    );
  }

  item(item) {
    const attrs = item.attributes || {};

    return (
      <HeaderListItem
        className={'Notification PrmCampaignItem' + (campaignState.isRead(item) ? '' : ' unread')}
        onclick={() => campaignState.markRead([item.id])}
        avatar={<span className="Avatar PrmCampaignItem-avatar">{this.forumInitial()}</span>}
        icon="fas fa-bullhorn"
        content={attrs.title}
        excerpt={attrs.body}
        datetime={attrs.createdAt ? new Date(attrs.createdAt) : undefined}
        href={attrs.linkUrl || null}
        actions={
          <Button
            className="Notification-action Button Button--link"
            icon="fas fa-times"
            title={t('dismiss')}
            onclick={(e) => {
              e.preventDefault();
              e.stopPropagation();
              campaignState.dismiss(item.id);
            }}
          />
        }
      />
    );
  }

  forumInitial() {
    const title = String(app.forum.attribute('title') || '?');
    return title.charAt(0).toUpperCase();
  }
}
