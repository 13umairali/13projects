'use strict';

/** Create + search actions. Responses are Payload objects ({id, type, attributes}). */

const flatten = (row) => (row && row.attributes ? { id: row.id, ...row.attributes } : row);

const createCustomer = {
  key: 'create_customer',
  noun: 'Customer',
  display: {
    label: 'Create Customer',
    description: 'Creates a new customer.',
  },
  operation: {
    inputFields: [
      { key: 'name', label: 'Name', type: 'string', required: true },
      { key: 'email', label: 'Email', type: 'string' },
      { key: 'phone', label: 'Phone', type: 'string' },
      { key: 'adresse', label: 'Address', type: 'string' },
      { key: 'country', label: 'Country', type: 'string' },
      { key: 'city', label: 'City', type: 'string' },
      { key: 'tax_number', label: 'Tax number', type: 'string' },
    ],
    perform: (z, bundle) =>
      z
        .request({
          method: 'POST',
          url: `${bundle.authData.workspace_url}/api/zapier/actions/customers`,
          body: bundle.inputData,
        })
        .then((res) => flatten(res.data)),
  },
};

const createProduct = {
  key: 'create_product',
  noun: 'Product',
  display: {
    label: 'Create Product',
    description: 'Creates a simple product (first category and unit are used as defaults).',
  },
  operation: {
    inputFields: [
      { key: 'name', label: 'Name', type: 'string', required: true },
      { key: 'price', label: 'Price', type: 'number', required: true },
      { key: 'cost', label: 'Cost', type: 'number' },
      { key: 'code', label: 'Code / SKU', type: 'string', helpText: 'Auto-generated when empty.' },
      { key: 'stock_alert', label: 'Stock alert threshold', type: 'number' },
      { key: 'note', label: 'Note', type: 'text' },
    ],
    perform: (z, bundle) =>
      z
        .request({
          method: 'POST',
          url: `${bundle.authData.workspace_url}/api/zapier/actions/products`,
          body: bundle.inputData,
        })
        .then((res) => flatten(res.data)),
  },
};

const findCustomer = {
  key: 'find_customer',
  noun: 'Customer',
  display: {
    label: 'Find Customer',
    description: 'Finds a customer by email, phone or name.',
  },
  operation: {
    inputFields: [{ key: 'query', label: 'Email, phone or name', type: 'string', required: true }],
    perform: (z, bundle) =>
      z
        .request({
          url: `${bundle.authData.workspace_url}/api/zapier/search/customers`,
          params: { query: bundle.inputData.query },
        })
        .then((res) => (res.data || []).map(flatten)),
  },
};

const findProduct = {
  key: 'find_product',
  noun: 'Product',
  display: {
    label: 'Find Product',
    description: 'Finds a product by code or name.',
  },
  operation: {
    inputFields: [{ key: 'query', label: 'Code or name', type: 'string', required: true }],
    perform: (z, bundle) =>
      z
        .request({
          url: `${bundle.authData.workspace_url}/api/zapier/search/products`,
          params: { query: bundle.inputData.query },
        })
        .then((res) => (res.data || []).map(flatten)),
  },
};

module.exports = { createCustomer, createProduct, findCustomer, findProduct };
