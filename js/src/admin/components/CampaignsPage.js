import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import Button from 'flarum/common/components/Button';
import Switch from 'flarum/common/components/Switch';
import saveSettings from 'flarum/admin/utils/saveSettings';

function t(key) {
  return app.translator.trans('prm-notification.admin.page.' + key);
}

function emptyDraft() {
  return {
    title: '',
    body: '',
    linkUrl: '',
    linkLabel: '',
    audienceNewUsers: false,
    audienceMembers: true,
    audienceGuests: false,
    isActive: true,
    startsAt: '',
    endsAt: '',
  };
}

export default class CampaignsPage extends ExtensionPage {
  oninit(vnode) {
    super.oninit(vnode);

    // ExtensionPage owns `body()`, `title`, `loading` etc. — keep campaign state namespaced.
    this.prm = {
      campaigns: [],
      campaignsLoading: true,
      campaignSaving: false,
      hoursSaving: false,
      editingId: null,
      hours: String(app.data.settings['prm-notification.new_user_hours'] ?? '72'),
      draft: emptyDraft(),
    };

    this.loadCampaigns();
  }

  resetDraft() {
    this.prm.draft = emptyDraft();
    this.prm.editingId = null;
  }

  loadCampaigns() {
    this.prm.campaignsLoading = true;
    app.store
      .find('prm-notification-campaigns')
      .then((items) => {
        this.prm.campaigns = items || [];
      })
      .catch(() => {
        this.prm.campaigns = [];
      })
      .finally(() => {
        this.prm.campaignsLoading = false;
        m.redraw();
      });
  }

  content() {
    const s = this.prm;

    return (
      <div className="ExtensionPage-settings">
        <div className="container PrmNotificationAdmin">
          <div className="Form-group">
            <label>{app.translator.trans('prm-notification.admin.settings.new_user_hours_label')}</label>
            <p className="helpText">{app.translator.trans('prm-notification.admin.settings.new_user_hours_help')}</p>
            <input
              className="FormControl"
              type="number"
              min="1"
              value={s.hours}
              oninput={(e) => (s.hours = e.target.value)}
            />
          </div>
          <div className="Form-group">
            <Button className="Button Button--primary" loading={s.hoursSaving} onclick={() => this.saveHours()}>
              {app.translator.trans('core.admin.settings.submit_button')}
            </Button>
          </div>

          <h3>{t('title')}</h3>
          {this.campaignForm()}
          <div className="PrmNotificationAdmin-list">{this.campaignList()}</div>
        </div>
      </div>
    );
  }

  textField(field, label, attrs = {}) {
    const d = this.prm.draft;
    return (
      <div className="Form-group">
        <label>{label}</label>
        <input className="FormControl" value={d[field]} oninput={(e) => (d[field] = e.target.value)} {...attrs} />
      </div>
    );
  }

  toggle(field, label, help) {
    const d = this.prm.draft;
    return (
      <div className="PrmNotificationAdmin-audienceRow">
        <Switch state={!!d[field]} onchange={(v) => (d[field] = !!v)}>
          {label}
        </Switch>
        {help ? <div className="helpText">{help}</div> : null}
      </div>
    );
  }

  campaignForm() {
    const d = this.prm.draft;

    return (
      <div className="PrmNotificationAdmin-form">
        {this.textField('title', t('title_field'))}
        <div className="Form-group">
          <label>{t('body_field')}</label>
          <textarea className="FormControl" rows="4" value={d.body} oninput={(e) => (d.body = e.target.value)} />
        </div>
        {this.textField('linkUrl', t('link_url'))}
        {this.textField('linkLabel', t('link_label'))}

        <div className="Form-group">
          <label>{t('audiences')}</label>
          <div className="PrmNotificationAdmin-audiences">
            {this.toggle('audienceNewUsers', t('audience_new_users'), t('audience_new_users_help'))}
            {this.toggle('audienceMembers', t('audience_members'), t('audience_members_help'))}
            {this.toggle('audienceGuests', t('audience_guests'), t('audience_guests_help'))}
          </div>
        </div>

        <div className="Form-group">{this.toggle('isActive', t('is_active'))}</div>

        {this.textField('startsAt', t('starts_at'), { type: 'datetime-local' })}
        {this.textField('endsAt', t('ends_at'), { type: 'datetime-local' })}

        <div className="Form-group">
          <Button className="Button Button--primary" loading={this.prm.campaignSaving} onclick={() => this.saveCampaign()}>
            {t('save')}
          </Button>
          {this.prm.editingId ? (
            <Button className="Button" onclick={() => this.resetDraft()}>
              {t('cancel')}
            </Button>
          ) : null}
        </div>
      </div>
    );
  }

  campaignList() {
    const s = this.prm;

    if (s.campaignsLoading) {
      return <p className="helpText">…</p>;
    }

    if (!s.campaigns.length) {
      return <p className="helpText">{t('empty')}</p>;
    }

    return (
      <div className="PrmNotificationAdmin-cards">
        {s.campaigns.map((c) => (
          <div className="PrmNotificationAdmin-card" key={c.id()}>
            <div className="PrmNotificationAdmin-cardTitle">{c.title()}</div>
            <div className="PrmNotificationAdmin-meta">{this.metaText(c)}</div>
            <p>{c.body()}</p>
            <div className="PrmNotificationAdmin-actions">
              <Button className="Button Button--link" onclick={() => this.edit(c)}>
                {t('edit')}
              </Button>
              <Button className="Button Button--link" onclick={() => this.remove(c)}>
                {t('delete')}
              </Button>
            </div>
          </div>
        ))}
      </div>
    );
  }

  metaText(c) {
    const parts = [c.isActive() ? t('status_active') : t('status_inactive')];
    if (c.audienceNewUsers()) parts.push(t('audience_new_users'));
    if (c.audienceMembers()) parts.push(t('audience_members'));
    if (c.audienceGuests()) parts.push(t('audience_guests'));
    return parts.join(' · ');
  }

  edit(c) {
    this.prm.editingId = c.id();
    this.prm.draft = {
      title: c.title() || '',
      body: c.body() || '',
      linkUrl: c.linkUrl() || '',
      linkLabel: c.linkLabel() || '',
      audienceNewUsers: !!c.audienceNewUsers(),
      audienceMembers: !!c.audienceMembers(),
      audienceGuests: !!c.audienceGuests(),
      isActive: c.isActive() !== false,
      startsAt: this.toLocalInput(c.startsAt()),
      endsAt: this.toLocalInput(c.endsAt()),
    };
  }

  toLocalInput(date) {
    if (!date) return '';
    const d = date instanceof Date ? date : new Date(date);
    if (isNaN(d.getTime())) return '';
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
  }

  toIso(local) {
    if (!local) return null;
    const d = new Date(local);
    return isNaN(d.getTime()) ? null : d.toISOString();
  }

  saveHours() {
    const s = this.prm;
    const value = String(parseInt(s.hours, 10) || 72);
    s.hoursSaving = true;
    saveSettings({ 'prm-notification.new_user_hours': value })
      .then(() => (s.hours = value))
      .finally(() => {
        s.hoursSaving = false;
        m.redraw();
      });
  }

  saveCampaign() {
    const s = this.prm;
    const d = s.draft;
    const attrs = {
      title: d.title.trim(),
      body: d.body.trim(),
      linkUrl: d.linkUrl.trim() || null,
      linkLabel: d.linkLabel.trim() || null,
      audienceNewUsers: !!d.audienceNewUsers,
      audienceMembers: !!d.audienceMembers,
      audienceGuests: !!d.audienceGuests,
      isActive: !!d.isActive,
      startsAt: this.toIso(d.startsAt),
      endsAt: this.toIso(d.endsAt),
    };

    if (!attrs.title || !attrs.body) return;

    s.campaignSaving = true;
    const existing = s.editingId ? app.store.getById('prm-notification-campaigns', s.editingId) : null;
    const record = existing || app.store.createRecord('prm-notification-campaigns');

    record
      .save(attrs)
      .then(() => {
        this.resetDraft();
        this.loadCampaigns();
      })
      .finally(() => {
        s.campaignSaving = false;
        m.redraw();
      });
  }

  remove(c) {
    if (!confirm('Delete this campaign?')) return;
    c.delete().then(() => this.loadCampaigns());
  }
}
