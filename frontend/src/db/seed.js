export const seedCategories = [
  { id: 1, name: 'Chicken Meals', sort_order: 1, is_active: 1 },
  { id: 2, name: 'Wings',         sort_order: 2, is_active: 1 },
  { id: 3, name: 'Snacks',        sort_order: 3, is_active: 1 },
  { id: 4, name: 'Beverages',     sort_order: 4, is_active: 1 },
]

export const seedProducts = [
  { id: 1,  category_id: 1, name: 'Solo Sulit Meal (2 pcs Boneless)',  price: 95,  stock_qty: 999, is_available: 1, is_active: 1, add_ons: [] },
  { id: 2,  category_id: 1, name: 'Boneless Chicken (1 pc)',           price: 50,  stock_qty: 999, is_available: 1, is_active: 1, add_ons: [] },
  { id: 3,  category_id: 1, name: 'Boneless Chicken (3 pcs)',          price: 140, stock_qty: 999, is_available: 1, is_active: 1, add_ons: [] },
  { id: 4,  category_id: 1, name: 'Crispy Liempo',                     price: 120, stock_qty: 999, is_available: 1, is_active: 1, add_ons: [] },
  { id: 5,  category_id: 2, name: 'Quick Bite Meal (2 pcs Wings)',     price: 85,  stock_qty: 999, is_available: 1, is_active: 1, add_ons: [] },
  { id: 6,  category_id: 2, name: 'Chicken Wings (1 pc)',              price: 45,  stock_qty: 999, is_available: 1, is_active: 1, add_ons: [] },
  { id: 7,  category_id: 2, name: 'Chicken Wings (3 pcs)',             price: 125, stock_qty: 999, is_available: 1, is_active: 1, add_ons: [] },
  { id: 8,  category_id: 3, name: 'French Fries (Regular)',            price: 35,  stock_qty: 999, is_available: 1, is_active: 1, add_ons: [] },
  { id: 9,  category_id: 3, name: 'French Fries (Large)',              price: 55,  stock_qty: 999, is_available: 1, is_active: 1, add_ons: [] },
  { id: 10, category_id: 3, name: 'Siomai (4 pcs)',                    price: 50,  stock_qty: 999, is_available: 1, is_active: 1, add_ons: [] },
  { id: 11, category_id: 3, name: 'Siomai (8 pcs)',                    price: 90,  stock_qty: 999, is_available: 1, is_active: 1, add_ons: [] },
  { id: 12, category_id: 4, name: 'Softdrinks (Regular)',              price: 15,  stock_qty: 999, is_available: 1, is_active: 1, add_ons: [] },
  { id: 13, category_id: 4, name: 'Softdrinks (Large)',                price: 25,  stock_qty: 999, is_available: 1, is_active: 1, add_ons: [] },
  { id: 14, category_id: 4, name: 'Bottled Water',                     price: 15,  stock_qty: 999, is_available: 1, is_active: 1, add_ons: [] },
]

export const seedCombos = [
  { id: 1, name: 'Solo Sulit + Drinks',     price: 110, is_active: 1 },
  { id: 2, name: 'Quick Bite + Drinks',     price: 100, is_active: 1 },
  { id: 3, name: 'Liempo + Fries + Drinks', price: 165, is_active: 1 },
]
