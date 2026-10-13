import Model from 'flarum/common/Model';

export default class Campaign extends Model {}

Object.assign(Campaign.prototype, {
  title: Model.attribute('title'),
  body: Model.attribute('body'),
  linkUrl: Model.attribute('linkUrl'),
  linkLabel: Model.attribute('linkLabel'),
  audienceNewUsers: Model.attribute('audienceNewUsers'),
  audienceMembers: Model.attribute('audienceMembers'),
  audienceGuests: Model.attribute('audienceGuests'),
  guestsSearchOnly: Model.attribute('guestsSearchOnly'),
  isActive: Model.attribute('isActive'),
  startsAt: Model.attribute('startsAt', Model.transformDate),
  endsAt: Model.attribute('endsAt', Model.transformDate),
  createdAt: Model.attribute('createdAt', Model.transformDate),
});
