'use strict';

/**
 * Custom auth: the user pastes their workspace URL (their tenant subdomain or
 * custom domain) and the API key generated in Settings → Zapier. Every request
 * carries the key in X-Api-Key; the test hits /me which also provides the
 * connection label (company name).
 */

const test = (z, bundle) =>
  z.request({ url: `${bundle.authData.workspace_url}/api/zapier/me` }).then((res) => res.data);

const includeApiKey = (request, z, bundle) => {
  if (bundle.authData.api_key) {
    request.headers = request.headers || {};
    request.headers['X-Api-Key'] = bundle.authData.api_key;
    request.headers.Accept = 'application/json';
  }
  return request;
};

module.exports = {
  config: {
    type: 'custom',
    fields: [
      {
        key: 'workspace_url',
        label: 'Workspace URL',
        type: 'string',
        required: true,
        helpText:
          'Your Stocky workspace address, e.g. `https://mystore.your-stocky-domain.com` (no trailing slash).',
      },
      {
        key: 'api_key',
        label: 'API Key',
        type: 'password',
        required: true,
        helpText: 'Generate it in **Settings → Zapier** inside your workspace.',
      },
    ],
    test,
    connectionLabel: (z, bundle) => bundle.inputData.company || 'Stocky workspace',
  },
  befores: [includeApiKey],
  afters: [],
};
