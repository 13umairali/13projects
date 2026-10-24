'use strict';

const { version } = require('./package.json');
const { version: platformVersion } = require('zapier-platform-core');
const authentication = require('./authentication');
const triggers = require('./triggers');
const { createCustomer, createProduct, findCustomer, findProduct } = require('./actions');

module.exports = {
  version,
  platformVersion,

  authentication: authentication.config,
  beforeRequest: [...authentication.befores],
  afterResponse: [...authentication.afters],

  triggers: Object.fromEntries(triggers.map((t) => [t.key, t])),

  creates: {
    [createCustomer.key]: createCustomer,
    [createProduct.key]: createProduct,
  },

  searches: {
    [findCustomer.key]: findCustomer,
    [findProduct.key]: findProduct,
  },
};
