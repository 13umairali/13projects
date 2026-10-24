'use strict';

const hookTrigger = require('./lib/hookTrigger');

/** The v1 trigger set — must stay in sync with ZapierController::TRIGGER_EVENTS. */
module.exports = [
  hookTrigger({
    key: 'new_sale',
    noun: 'Sale',
    label: 'New Sale',
    description: 'Triggers when a sale is created (POS or invoices).',
    event: 'sale.created',
  }),
  hookTrigger({
    key: 'new_quotation',
    noun: 'Quotation',
    label: 'New Quotation',
    description: 'Triggers when a quotation is created.',
    event: 'quotation.created',
  }),
  hookTrigger({
    key: 'new_customer',
    noun: 'Customer',
    label: 'New Customer',
    description: 'Triggers when a customer is created.',
    event: 'client.created',
  }),
  hookTrigger({
    key: 'new_product',
    noun: 'Product',
    label: 'New Product',
    description: 'Triggers when a product is created.',
    event: 'product.created',
  }),
  hookTrigger({
    key: 'new_expense',
    noun: 'Expense',
    label: 'New Expense',
    description: 'Triggers when an expense is recorded.',
    event: 'expense.created',
  }),
  hookTrigger({
    key: 'new_payment',
    noun: 'Payment',
    label: 'New Payment Received',
    description: 'Triggers when a sale payment is recorded.',
    event: 'payment.created',
  }),
  hookTrigger({
    key: 'low_stock',
    noun: 'Product',
    label: 'Low Stock Alert',
    description: "Triggers when a sale pushes a product to (or below) its stock alert threshold.",
    event: 'product.low_stock',
  }),
];
