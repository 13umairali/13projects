'use strict';

/**
 * Factory for REST-hook triggers. All Stocky triggers share the same shape:
 * subscribe POSTs the target URL + event, unsubscribe DELETEs the hook id,
 * perform unwraps the delivery envelope ({id, event, created_at, data}), and
 * performList feeds the Zap editor from GET /samples/{event}.
 */

const flatten = (row) => {
  if (row && typeof row === 'object' && row.attributes) {
    return { id: row.id, ...row.attributes };
  }
  return { id: row.product_id || row.id || 0, ...row };
};

module.exports = ({ key, noun, label, description, event }) => ({
  key,
  noun,
  display: { label, description },
  operation: {
    type: 'hook',

    performSubscribe: (z, bundle) =>
      z
        .request({
          method: 'POST',
          url: `${bundle.authData.workspace_url}/api/zapier/hooks`,
          body: { target_url: bundle.targetUrl, event },
        })
        .then((res) => res.data),

    performUnsubscribe: (z, bundle) =>
      z
        .request({
          method: 'DELETE',
          url: `${bundle.authData.workspace_url}/api/zapier/hooks/${bundle.subscribeData.id}`,
        })
        .then((res) => res.data),

    perform: (z, bundle) => {
      const envelope = bundle.cleanedRequest || {};
      const data = envelope.data || envelope;
      return [{ delivery_id: envelope.id, event: envelope.event, ...flatten(data) }];
    },

    performList: (z, bundle) =>
      z
        .request({ url: `${bundle.authData.workspace_url}/api/zapier/samples/${event}` })
        .then((res) => (res.data || []).map(flatten)),
  },
});
