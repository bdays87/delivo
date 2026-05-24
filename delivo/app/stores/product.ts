import { defineStore } from 'pinia';
import type { Category, CategoryParent } from './category';

export type ProductStatus = 'PENDING' | 'ACTIVE' | 'REJECTED' | 'ARCHIVED';

export interface ProductImage {
  id: number;
  disk: string;
  path: string;
  sort_order: number;
  is_primary: boolean;
}

export interface ProductPriceTier {
  id?: number;
  min_qty: number;
  unit_price: string | number;
}

export interface ProductVariant {
  id?: number;
  color: string | null;
  stock_quantity: number;
  sku: string | null;
  image_path?: string | null;
}

export interface ProductVendorRef {
  id: number;
  business_name: string;
  slug: string;
}

export interface Product {
  id: number;
  vendor_id: number;
  category_id: number;
  name: string;
  slug: string;
  description: string | null;
  sku: string | null;
  weight_kg: string | number | null;
  affiliate_influencer_pct?: string | number;
  affiliate_buyer_discount_pct?: string | number;
  status: ProductStatus;
  submitted_at: string | null;
  approved_at: string | null;
  rejected_at: string | null;
  rejection_reason: string | null;
  category?: Pick<Category, 'id' | 'name' | 'slug'> | CategoryParent;
  vendor?: ProductVendorRef;
  images?: ProductImage[];
  price_tiers?: ProductPriceTier[];
  variants?: ProductVariant[];
}

// Convenience helpers
export const productImageUrl = (image?: ProductImage | null): string | null => {
  if (!image) return null;
  const cfg = useRuntimeConfig();
  const base = cfg.public.apiBase as string;
  return `${base}/storage/${image.path}`;
};

export const productPrimaryImage = (product?: Product | null): ProductImage | null => {
  if (!product?.images?.length) return null;
  return product.images.find((i) => i.is_primary) ?? product.images[0];
};
