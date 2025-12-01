export type Language = 'tr' | 'en' | 'ar';
export type Theme = 'dark' | 'light';

export interface Translation {
  start: string;
  searchPlaceholder: string;
  categories: string;
  submit: string;
  feedbackTitle: string; // "Görüş Bildirin"
  selectBranch: string;
  selectTopic: string;
  yourMessage: string;
  contactDetails: string;
  feedbackSuccess: string;
  close: string;
  popular: string;
  currency: string;
  wifiTitle: string;
  wifiSelectBranch: string;
  wifiPasswordIs: string;
  followUs: string;
  recoveryMessage: string;
  rateUs: string;
  continue: string;
  copy: string;
  copied: string;
}

export type FeedbackTopic = 'taste' | 'service' | 'staff' | 'hygiene' | 'other';

export interface Branch {
  id: string;
  name: string;
  wifiPassword?: string;
}

export interface Category {
  id: string;
  name: Record<Language, string>;
  image: string;
  parentId?: string | null;
}

export interface Product {
  id: string;
  categoryId: string;
  name: Record<Language, string>;
  description: Record<Language, string>;
  price: number;
  image: string;
  isPopular?: boolean;
}