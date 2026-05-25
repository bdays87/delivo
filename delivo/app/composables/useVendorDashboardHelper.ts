export interface VendorDashboardSummary {
  total_products: number;
  active_products: number;
  pending_products: number;
  total_orders: number;
  pending_payment_orders: number;
  delivered_orders: number;
  total_sales_usd: string;
  commission_paid_usd: string;
  net_after_commission_usd: string;
  orders_available: boolean;
}

export interface VendorDashboardStock {
  total_units: number;
  variant_count: number;
  low_stock_count: number;
  out_of_stock_count: number;
  healthy_count: number;
}

export interface VendorTopMovingProduct {
  id: number;
  name: string;
  slug: string;
  status: string;
  category_name: string | null;
  image_path: string | null;
  units_sold: number;
  revenue_usd: string;
  stock_remaining: number;
  low_stock_variants: number;
  movement_score: number;
  movement_rank: number;
}

export interface VendorStockByProduct {
  id: number;
  name: string;
  status: string;
  total_stock: number;
  variant_count: number;
  low_stock_variants: number;
  out_of_stock_variants: number;
}

export interface VendorDashboardData {
  summary: VendorDashboardSummary;
  stock: VendorDashboardStock;
  top_moving: VendorTopMovingProduct[];
  stock_by_product: VendorStockByProduct[];
}

export const useVendorDashboardHelper = () => {
  const client = useSanctumClient();

  const getDashboard = async () => {
    try {
      const data = await client('/api/v1/vendor/me/dashboard', { method: 'GET' });
      return { data: ref(data), error: ref(null) };
    } catch (err) {
      return { data: ref(null), error: ref(err) };
    }
  };

  return { getDashboard };
};
