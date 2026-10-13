import Model from 'flarum/common/Model';

export default class Campaign extends Model {}

Object.assign(Campaign.prototype, {
  title: Model.attribute('title'),
  body: Model.attribute('body'),
  linkUrl: Model.attribute('linkUrl'),
  linkLabel: Model.attribute('linkLabel'),
  createdAt: Model.attribute('createdAt', Model.transformDate),
});
