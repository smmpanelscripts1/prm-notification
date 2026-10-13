import app from 'flarum/forum/app';
import { guestKey } from '../utils';

class CampaignState {
  constructor() {
    this.items = [];
    this.loading = false;
    this.loaded = false;
  }

  headers() {
    const headers = {};
    if (!app.session.user) {
      const key = guestKey();
      if (key) headers['X-PRM-Guest-Key'] = key;
    }
    return headers;
  }

  load() {
    if (this.loading) return Promise.resolve();

    this.loading = true;

    return app
      .request({
        method: 'GET',
        url: app.forum.attribute('apiUrl') + '/prm-notifications',
        headers: this.headers(),
      })
      .then((payload) => {
        this.items = (payload && payload.data) || [];
      })
      .catch(() => {
        this.items = [];
      })
      .finally(() => {
        this.loading = false;
        this.loaded = true;
        m.redraw();
      });
  }

  count() {
    return this.items.length;
  }

  unreadCount() {
    return this.items.filter((item) => !this.isRead(item)).length;
  }

  isRead(item) {
    return !!(item.attributes && item.attributes.isRead);
  }

  markRead(ids = null) {
    const targets = this.items.filter((item) => !this.isRead(item) && (!ids || ids.map(String).includes(String(item.id))));
    if (!targets.length) return Promise.resolve();

    targets.forEach((item) => {
      item.attributes = Object.assign({}, item.attributes, { isRead: true });
    });
    m.redraw();

    const body = { ids: targets.map((item) => item.id) };
    if (!app.session.user) {
      body.guestKey = guestKey();
    }

    return app.request({
      method: 'POST',
      url: app.forum.attribute('apiUrl') + '/prm-notifications/read',
      headers: this.headers(),
      body,
    });
  }

  dismiss(id) {
    this.items = this.items.filter((item) => String(item.id) !== String(id));
    m.redraw();

    const body = {};
    if (!app.session.user) {
      body.guestKey = guestKey();
    }

    return app.request({
      method: 'POST',
      url: app.forum.attribute('apiUrl') + '/prm-notifications/' + id + '/dismiss',
      headers: this.headers(),
      body,
    });
  }
}

const campaignState = new CampaignState();

export default campaignState;
