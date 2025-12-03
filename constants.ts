import { Branch, Category, Product, Translation, Language } from './types';

// Ortak logo yolu: Hostinger'a yüklenen PNG/SVG'yi buraya koyabilirsiniz.
export const LOGO_URL = '/saray-logo.png';

export const TRANSLATIONS: Record<Language, Translation> = {
  tr: {
    start: "BAŞLAMAK İÇİN TIKLAYIN",
    searchPlaceholder: "Menüde lezzet ara...",
    categories: "KATEGORİLER",
    submit: "GÖNDER",
    feedbackTitle: "Görüş Bildirin",
    selectBranch: "Hangi şubemizdesiniz?",
    selectTopic: "Konu Seçiniz",
    yourMessage: "Deneyiminizi paylaşın...",
    contactDetails: "E-posta veya Telefon (İsteğe bağlı)",
    feedbackSuccess: "Teşekkürler, iletildi.",
    close: "Kapat",
    popular: "Şefin İmzası",
    currency: "₺",
    wifiTitle: "Wi-Fi Bağlantısı",
    wifiSelectBranch: "Şifre için şube seçiniz",
    wifiPasswordIs: "Wi-Fi Şifresi:",
    followUs: "Bizi Takip Edin",
    recoveryMessage: "Size bunu nasıl telafi edebiliriz?",
    rateUs: "Memnun kaldınız mı?",
    continue: "Devam",
    copy: "Kopyala",
    copied: "Kopyalandı",
    searchResults: "Arama sonuçları"
  },
  en: {
    start: "CLICK TO START",
    searchPlaceholder: "Search menu...",
    categories: "MENUS",
    submit: "SUBMIT",
    feedbackTitle: "Give Feedback",
    selectBranch: "Select Branch",
    selectTopic: "Select Topic",
    yourMessage: "Share your experience...",
    contactDetails: "Contact Info (Optional)",
    feedbackSuccess: "Thank you, sent.",
    close: "Close",
    popular: "Chef's Signature",
    currency: "TL",
    wifiTitle: "Wi-Fi Connection",
    wifiSelectBranch: "Select branch for password",
    wifiPasswordIs: "Wi-Fi Password:",
    followUs: "Follow Us",
    recoveryMessage: "How can we make it up to you?",
    rateUs: "Satisfied?",
    continue: "Continue",
    copy: "Copy",
    copied: "Copied",
    searchResults: "Search results"
  },
  ar: {
    start: "اضغط للبدء",
    searchPlaceholder: "بحث...",
    categories: "القوائم",
    submit: "إرسال",
    feedbackTitle: "ملاحظاتكم",
    selectBranch: "اختر الفرع",
    selectTopic: "اختر الموضوع",
    yourMessage: "شاركنا تجربتك...",
    contactDetails: "معلومات الاتصال",
    feedbackSuccess: "شكرا لك",
    close: "إغلاق",
    popular: "توقيع الشيف",
    currency: "ليرة",
    wifiTitle: "واي فاي",
    wifiSelectBranch: "اختر الفرع لكلمة المرور",
    wifiPasswordIs: "كلمة المرور:",
    followUs: "تابعنا",
    recoveryMessage: "كيف يمكننا تعويض ذلك؟",
    rateUs: "هل أنت راض؟",
    continue: "متابعة",
    copy: "نسخ",
    copied: "تم النسخ",
    searchResults: "نتائج البحث"
  }
};

export const BRANCHES: Branch[] = [
  { id: '1', name: 'Merkez (Gültepe)', wifiPassword: 'SarayMerkez2024' },
  { id: '2', name: 'Petrol City AVM', wifiPassword: 'PetrolCityGuest' },
  { id: '3', name: 'Kıra Dağı', wifiPassword: 'KiraDagiManzara' },
];

export const CATEGORIES: Category[] = [
  { id: 'cat1', name: { tr: 'Kahvaltılıklar', en: 'Breakfast', ar: 'إفطار' }, image: 'https://images.unsplash.com/photo-1533089862017-5614ec45e25a?auto=format&fit=crop&q=80&w=400' },
  { id: 'cat2', name: { tr: 'Ana Yemekler', en: 'Main Course', ar: 'الأطباق الرئيسية' }, image: 'https://images.unsplash.com/photo-1544025162-d76690b68f11?auto=format&fit=crop&q=80&w=400' },
  { id: 'cat3', name: { tr: 'Tatlılar', en: 'Desserts', ar: 'حلويات' }, image: 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?auto=format&fit=crop&q=80&w=400' },
  { id: 'cat4', name: { tr: 'Sıcak İçecekler', en: 'Hot Drinks', ar: 'مشروبات ساخنة' }, image: 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&q=80&w=400' },
  { id: 'cat5', name: { tr: 'Soğuk İçecekler', en: 'Cold Drinks', ar: 'مشروبات باردة' }, image: 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&q=80&w=400' },
  { id: 'cat6', name: { tr: 'Aperatifler', en: 'Snacks', ar: 'وجبات خفيفة' }, image: 'https://images.unsplash.com/photo-1628840042765-356cda07504e?auto=format&fit=crop&q=80&w=400' },
];

export const PRODUCTS: Product[] = [
  {
    id: 'p1',
    categoryId: 'cat1',
    name: { tr: 'Serpme Kahvaltı (2 Kişilik)', en: 'Spread Breakfast (For 2)', ar: 'إفطار متنوع (لشخصين)' },
    description: { tr: 'Yöresel peynirler, organik bal, kaymak, ev yapımı reçeller, sahanda yumurta ve sınırsız çay ile.', en: 'Local cheeses, organic honey, cream, homemade jams, fried eggs and unlimited tea.', ar: 'أجبان محلية، عسل عضوي، قشطة، مربيات منزلية، بيض مقلي وشاي غير محدود.' },
    price: 650,
    image: 'https://images.unsplash.com/photo-1629245536417-640b3c67530e?auto=format&fit=crop&q=80&w=800',
    isPopular: true
  },
  {
    id: 'p2',
    categoryId: 'cat2',
    name: { tr: 'Saray Kebabı', en: 'Palace Kebab', ar: 'كباب القصر' },
    description: { tr: 'Közlenmiş patlıcan yatağında, özel soslu kuzu eti, kibrit patates ve yoğurt.', en: 'Lamb meat with special sauce on roasted eggplant, matchstick potatoes and yogurt.', ar: 'لحم ضأن بصلصة خاصة على باذنجان مشوي، بطاطس عيدان وزبادي.' },
    price: 480,
    image: 'https://images.unsplash.com/photo-1603360946369-dc9bb6f54511?auto=format&fit=crop&q=80&w=800',
    isPopular: true
  },
  {
    id: 'p3',
    categoryId: 'cat3',
    name: { tr: 'Fıstıklı Katmer', en: 'Pistachio Katmer', ar: 'كاتمر بالفستق' },
    description: { tr: 'İncecik hamur, bol Antep fıstığı ve kaymak. Sıcak servis edilir.', en: 'Thin dough, plenty of pistachios and cream. Served hot.', ar: 'عجين رقيق، الكثير من الفستق والقشطة. يقدم ساخناً.' },
    price: 240,
    image: 'https://images.unsplash.com/photo-1576014131341-fe1486aa247c?auto=format&fit=crop&q=80&w=800'
  },
  {
    id: 'p4',
    categoryId: 'cat4',
    name: { tr: 'Menengiç Kahvesi', en: 'Menengic Coffee', ar: 'قهوة مينينجيتش' },
    description: { tr: 'Güneydoğu\'nun meşhur yabani fıstık kahvesi, süt ile hazırlanır.', en: 'Famous wild pistachio coffee of the Southeast, prepared with milk.', ar: 'قهوة الفستق البري الشهيرة في الجنوب الشرقي، محضرة بالحليب.' },
    price: 90,
    image: 'https://images.unsplash.com/photo-1596911571433-28c8c5c56780?auto=format&fit=crop&q=80&w=800'
  }
];