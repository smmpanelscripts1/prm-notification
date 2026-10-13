import app from 'flarum/forum/app';

const GUEST_KEY = 'prmNotificationGuestKey';

export function guestKey() {
  try {
    let key = localStorage.getItem(GUEST_KEY);
    if (!key) {
      key = 'g_' + Math.random().toString(36).slice(2) + Date.now().toString(36);
      localStorage.setItem(GUEST_KEY, key);
    }
    return key;
  } catch (e) {
    return null;
  }
}

export function t(key, params = {}) {
  return app.translator.trans('prm-notification.forum.' + key, params);
}
