export const EXCHANGE_RATES_TO_USD: Record<string, number> = {
  USD: 1.0,
  EUR: 0.92,
  GBP: 0.79,
  UGX: 3750.0,
  KES: 129.0,
  TZS: 2620.0,
  RWF: 1380.0,
};

export const CURRENCY_OPTIONS = [
  { value: 'UGX', label: 'UGX — Ugandan Shilling', badge: 'UGX', icon: 'fa-solid fa-coins' },
  { value: 'KES', label: 'KES — Kenyan Shilling', badge: 'KES', icon: 'fa-solid fa-coins' },
  { value: 'TZS', label: 'TZS — Tanzanian Shilling', badge: 'TZS', icon: 'fa-solid fa-coins' },
  { value: 'RWF', label: 'RWF — Rwandan Franc', badge: 'RWF', icon: 'fa-solid fa-coins' },
  { value: 'USD', label: 'USD — US Dollar', badge: 'USD', icon: 'fa-solid fa-dollar-sign' },
  { value: 'EUR', label: 'EUR — Euro', badge: 'EUR', icon: 'fa-solid fa-euro-sign' },
  { value: 'GBP', label: 'GBP — British Pound', badge: 'GBP', icon: 'fa-solid fa-sterling-sign' },
];

export function convertCurrencyAmount(amount: number, fromCurrency: string, toCurrency: string): number {
  const from = (fromCurrency || 'UGX').toUpperCase();
  const to = (toCurrency || 'UGX').toUpperCase();

  if (from === to || !amount || isNaN(amount)) {
    return amount || 0;
  }

  const fromRate = EXCHANGE_RATES_TO_USD[from] ?? 1.0;
  const toRate = EXCHANGE_RATES_TO_USD[to] ?? 1.0;

  const usdAmount = amount / fromRate;
  const converted = usdAmount * toRate;

  if (['UGX', 'TZS', 'RWF'].includes(to)) {
    return Math.round(converted);
  }

  return Math.round(converted * 100) / 100;
}

export function formatCurrencyAmount(amount: number, currencyCode: string = 'UGX'): string {
  const code = (currencyCode || 'UGX').toUpperCase();
  const maxFractionDigits = ['UGX', 'TZS', 'RWF', 'KES'].includes(code) ? 0 : 2;

  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: code,
    maximumFractionDigits: maxFractionDigits,
  }).format(amount || 0);
}

