import React, { useEffect, useMemo, useRef, useState } from 'react';
import { HashRouter, Routes, Route, useNavigate } from 'react-router-dom';
import { AppProvider, useApp } from './context';
import { Logo } from './components/Logo';
import { LanguageSwitcher } from './components/LanguageSwitcher';
import { FeedbackModal, FeedbackToggle } from './components/FeedbackModal';
import { ProductModal } from './components/ProductModal';
import { BRANCHES as STATIC_BRANCHES } from './constants';
import { motion, AnimatePresence } from 'framer-motion';
import { Wifi, Instagram, Moon, Sun, X, Copy, Check } from 'lucide-react';
import { Product, Branch, Campaign } from './types';

// --- Components ---

const ThemeToggle = () => {
  const { theme, toggleTheme } = useApp();
  return (
    <button 
      onClick={toggleTheme}
      className="p-2.5 rounded-full text-saray-gold hover:bg-stone-100 dark:hover:bg-white/10 transition-colors"
      aria-label="Toggle Theme"
    >
      {theme === 'dark' ? <Moon size={20} /> : <Sun size={20} />}
    </button>
  );
};

const WifiModal: React.FC<{ isOpen: boolean; onClose: () => void; branches: Branch[] }> = ({ isOpen, onClose, branches }) => {
  const { translate } = useApp();
  const [selectedBranch, setSelectedBranch] = useState<Branch | null>(null);
  const [copied, setCopied] = useState(false);

  const handleCopy = () => {
    if (selectedBranch?.wifiPassword) {
      navigator.clipboard.writeText(selectedBranch.wifiPassword);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    }
  };

  if (!isOpen) return null;

  return (
    <AnimatePresence>
      <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
        <motion.div 
            initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
            onClick={onClose} className="absolute inset-0 bg-black/80 backdrop-blur-sm"
        />
        <motion.div 
            initial={{ scale: 0.9, y: 20 }} animate={{ scale: 1, y: 0 }} exit={{ scale: 0.9, y: 20 }}
            className="relative w-full max-w-sm bg-white dark:bg-saray-surface border border-saray-gold/30 rounded-2xl p-6 shadow-2xl"
        >
            <button onClick={onClose} className="absolute top-4 right-4 text-stone-400 hover:text-saray-gold">
                <X size={20} />
            </button>
            
            <div className="text-center mb-6">
                <div className="inline-flex p-3 rounded-full bg-saray-gold/10 text-saray-gold mb-3">
                    <Wifi size={32} />
                </div>
                <h3 className="font-serif text-xl dark:text-white text-stone-900 font-bold">{translate('wifiTitle')}</h3>
            </div>

            {!selectedBranch ? (
                <div className="space-y-2">
                    <p className="text-xs uppercase tracking-widest text-center text-stone-500 mb-4">{translate('wifiSelectBranch')}</p>
                    {branches.map(branch => (
                        <button
                            key={branch.id}
                            onClick={() => setSelectedBranch(branch)}
                            className="w-full p-4 rounded-xl bg-stone-50 dark:bg-white/5 border border-stone-200 dark:border-white/10 hover:border-saray-gold text-left transition-all group"
                        >
                            <span className="font-serif font-bold text-stone-800 dark:text-saray-text group-hover:text-saray-gold transition-colors">{branch.name}</span>
                        </button>
                    ))}
                </div>
            ) : (
                <div className="text-center animate-in fade-in slide-in-from-bottom-2">
                    <p className="text-sm text-stone-500 mb-2">{selectedBranch.name}</p>
                    <div className="bg-stone-100 dark:bg-black/30 p-4 rounded-xl border border-dashed border-saray-gold mb-4">
                        <p className="text-xs text-saray-gold mb-1">{translate('wifiPasswordIs')}</p>
                        <p className="font-mono text-xl font-bold dark:text-white text-stone-900 tracking-wider">{selectedBranch.wifiPassword}</p>
                    </div>
                    <button 
                        onClick={handleCopy}
                        className="flex items-center justify-center gap-2 w-full py-3 bg-saray-gold text-saray-black font-bold rounded-lg hover:bg-saray-darkGold transition-colors"
                    >
                        {copied ? <Check size={18} /> : <Copy size={18} />}
                        {copied ? translate('copied') : translate('copy')}
                    </button>
                    <button 
                        onClick={() => setSelectedBranch(null)} 
                        className="mt-4 text-xs text-stone-500 underline"
                    >
                        {translate('wifiSelectBranch')}
                    </button>
                </div>
            )}
        </motion.div>
      </div>
    </AnimatePresence>
  );
};

const SplashScreen = () => {
  const { translate, theme } = useApp();
  const navigate = useNavigate();
  const isDark = theme === 'dark';

  return (
    <div
      className={`min-h-screen relative flex flex-col items-center justify-center p-6 overflow-hidden transition-colors duration-700 ease-out ${
        isDark ? 'bg-saray-black' : 'bg-white'
      }`}
    >
      {/* Enhanced Background */}
      <div className="absolute inset-0 bg-noise opacity-10"></div>
      <AnimatePresence mode="wait">
        <motion.div
          key={isDark ? 'dark-backdrop' : 'light-backdrop'}
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          transition={{ duration: 0.8, ease: 'easeInOut' }}
          className={`absolute inset-0 ${
            isDark
              ? 'bg-gradient-to-b from-saray-black via-[#1a1500] to-saray-black'
              : 'bg-gradient-to-b from-white via-white to-white'
          }`}
        />
      </AnimatePresence>
      <div className={`absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] ${isDark ? 'from-saray-gold/12' : 'from-black/5'} via-transparent to-transparent opacity-60 transition-opacity duration-700`}></div>
      
      {/* Top Bar */}
      <div className="absolute top-0 left-0 right-0 p-6 flex justify-between items-start z-20">
         <ThemeToggle />
         <LanguageSwitcher />
      </div>

        <div className="z-10 w-full max-w-xl text-center flex flex-col items-center h-full justify-center gap-8">

          <div className="flex flex-col items-center animate-float w-[82vw] max-w-[520px] h-auto mx-auto">
              <Logo variant={isDark ? 'dark' : 'light'} size="lg" className="w-full h-auto" />
          </div>

        <motion.button
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.5, duration: 0.8 }}
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
            onClick={() => navigate('/menu')}
            className="group relative px-10 py-4 bg-transparent overflow-hidden"
        >
            <div className="absolute inset-0 border border-saray-gold/30 rounded-sm"></div>
            <div className="absolute inset-0 bg-saray-gold/5 group-hover:bg-saray-gold/10 transition-all duration-500"></div>
            <div className="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-saray-gold to-transparent opacity-50"></div>
            <div className="absolute bottom-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-saray-gold to-transparent opacity-50"></div>
            
            <span className="relative font-serif text-saray-gold font-bold tracking-[0.2em] text-sm sm:text-base group-hover:text-white transition-colors">
              {translate('start')}
            </span>
        </motion.button>
      </div>
    </div>
  );
};

const MENU_CACHE_KEY = 'saray_menu_cache_v2';

type CachedMenu = {
  version: number;
  categories: { id: string; name: any; image: string }[];
  products: Product[];
  branches: Branch[];
  campaign: Campaign;
  timestamp: number;
};

const safeStorageGet = (key: string): string | null => {
  try {
    if (typeof window === 'undefined' || !window.localStorage) return null;
    return window.localStorage.getItem(key);
  } catch (e) {
    return null;
  }
};

const safeStorageSet = (key: string, value: string) => {
  try {
    if (typeof window === 'undefined' || !window.localStorage) return;
    window.localStorage.setItem(key, value);
  } catch (e) {
    // storage disabled; ignore silently
  }
};

const readCachedMenu = (): CachedMenu | null => {
  const raw = safeStorageGet(MENU_CACHE_KEY);
  if (!raw) return null;
  try {
    return JSON.parse(raw) as CachedMenu;
  } catch (e) {
    console.warn('Önbellek okunamadı', e);
    return null;
  }
};

const MenuScreen = () => {
  const { translate, language, theme } = useApp();
  const [showFeedback, setShowFeedback] = useState(false);
  const [showWifi, setShowWifi] = useState(false);
  const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);
  const [selectedCatId, setSelectedCatId] = useState<string | null>(null);
  const cached = readCachedMenu();
  const [categories, setCategories] = useState<{ id: string; name: any; image: string }[]>(cached?.categories ?? []);
  const [products, setProducts] = useState<Product[]>(cached?.products ?? []);
  const [branches, setBranches] = useState<Branch[]>(cached?.branches ?? STATIC_BRANCHES);
  const [campaign, setCampaign] = useState<Campaign>(cached?.campaign ?? { active: false, image: null });
  const [showCampaign, setShowCampaign] = useState<boolean>(false);
  const [isLoadingData, setIsLoadingData] = useState(!cached);
  const [apiError, setApiError] = useState<string | null>(null);
  const [searchTerm, setSearchTerm] = useState('');

  const campaignSeenKey = 'campaign_seen_v1';

  const hasSeenCampaign = (image?: string | null) => {
    if (!image) return false;
    try {
      const raw = safeStorageGet(campaignSeenKey);
      if (!raw) return false;
      const parsed = JSON.parse(raw);
      return parsed?.image === image;
    } catch (e) {
      return false;
    }
  };

  const markCampaignSeen = (image?: string | null) => {
    if (!image) return;
    safeStorageSet(campaignSeenKey, JSON.stringify({ image, ts: Date.now() }));
  };

  useEffect(() => {
    let cancelled = false;

    const mapPayload = (payload: any) => {
      const mappedCats = Array.isArray(payload.categories)
        ? payload.categories.map((cat: any) => ({
            id: String(cat.id),
            name: typeof cat.name === 'object' ? cat.name : { tr: cat.name, en: cat.name, ar: cat.name },
            image: cat.image || cat.image_path || '',
          }))
        : [];

      const mappedProducts = Array.isArray(payload.products)
        ? payload.products.map((p: any) => ({
            id: String(p.id),
            categoryId: String(p.categoryId ?? p.category_id ?? ''),
            name: typeof p.name === 'object' ? p.name : { tr: p.name, en: p.name, ar: p.name },
            description: typeof p.description === 'object'
              ? p.description
              : { tr: p.description ?? '', en: p.description ?? '', ar: p.description ?? '' },
            price: Number(p.price ?? 0),
            image: p.image || p.image_url || p.image_path || '',
            isPopular: Boolean(p.isPopular ?? false),
          }))
        : [];

      const mappedBranches = Array.isArray(payload.branches) && payload.branches.length
        ? payload.branches.map((b: any) => ({
            id: String(b.id),
            name: b.name,
            wifiPassword: b.wifiPassword ?? b.wifi_password ?? '',
          }))
        : STATIC_BRANCHES;

      const mappedCampaign: Campaign = {
        active: Boolean(payload?.campaign?.active && payload?.campaign?.image),
        image: payload?.campaign?.image ?? null,
      };

      return {
        mappedCats,
        mappedProducts,
        mappedBranches,
        mappedCampaign,
        version: Number(payload.version ?? 1),
      };
    };

    const resolveApiUrl = (path: string) => {
      const clean = path.replace(/^\//, '');
      if (typeof window === 'undefined') return `/${clean}`;
      try {
        return new URL(`./${clean}`, window.location.href).toString();
      } catch (err) {
        return `/${clean}`;
      }
    };

    const loadData = async () => {
      try {
        const apiUrl = resolveApiUrl('admin/api/menu.php');
        const response = await fetch(apiUrl, { cache: 'no-store' });
        if (!response.ok) {
          throw new Error('Sunucu hatası');
        }
        const payload = await response.json();
        if (cancelled) return;

        const { mappedCats, mappedProducts, mappedBranches, mappedCampaign, version } = mapPayload(payload);
        setCategories(mappedCats);
        setProducts(mappedProducts);
        setBranches(mappedBranches);
        setCampaign(mappedCampaign);
        const shouldShow = mappedCampaign.active && mappedCampaign.image && !hasSeenCampaign(mappedCampaign.image);
        setShowCampaign(shouldShow);
        setIsLoadingData(false);
        setApiError(null);

        if (typeof window !== 'undefined') {
          const cache: CachedMenu = {
            version,
            categories: mappedCats,
            products: mappedProducts,
            branches: mappedBranches,
            campaign: mappedCampaign,
            timestamp: Date.now(),
          };
          safeStorageSet(MENU_CACHE_KEY, JSON.stringify(cache));
        }
      } catch (error) {
        console.error('Menü verisi alınamadı', error);
        setApiError('Menü verisi alınamadı, lütfen bağlantınızı kontrol edin.');
        if (!cached) {
          setCategories([]);
          setProducts([]);
          setBranches(STATIC_BRANCHES);
          setIsLoadingData(false);
        }
      }

      return () => {
        cancelled = true;
      };
    };

    // Önbellekteki veri anında yüklensin, ardından arka planda tazele
    if (cached) {
      setCategories(cached.categories);
      setProducts(cached.products);
      setBranches(cached.branches.length ? cached.branches : STATIC_BRANCHES);
      setCampaign(cached.campaign ?? { active: false, image: null });
      const shouldShowCached = cached.campaign?.active && cached.campaign?.image && !hasSeenCampaign(cached.campaign.image);
      setShowCampaign(Boolean(shouldShowCached));
      setIsLoadingData(false);
    }

    loadData();

    return () => {
      cancelled = true;
    };
  }, []);

  const normalizedSearch = searchTerm.trim().toLowerCase();

  const activeProducts = useMemo(() => {
    if (selectedCatId) {
      return products.filter(p => p.categoryId === selectedCatId);
    }
    return products;
  }, [products, selectedCatId, normalizedSearch]);

  const displayedProducts = activeProducts.filter((p) => {
    const name = p.name[language]?.toLowerCase?.() ?? '';
    const desc = p.description?.[language]?.toLowerCase?.() ?? '';
    return name.includes(normalizedSearch) || desc.includes(normalizedSearch);
  });

  const isSearching = normalizedSearch.length > 0;

  return (
    <div className="min-h-screen bg-stone-50 dark:bg-saray-black pb-24 relative transition-colors duration-500">
      <div className="fixed inset-0 bg-noise opacity-[0.03] pointer-events-none z-0"></div>

      {showCampaign && campaign.active && campaign.image && (
        <div className="fixed inset-0 z-50 flex items-center justify-center px-6">
          <div
            className="absolute inset-0 bg-black/80 backdrop-blur-sm"
            onClick={() => {
              markCampaignSeen(campaign.image);
              setShowCampaign(false);
            }}
          ></div>
          <div className="relative w-full max-w-[92vw] max-h-[88vh] border-[0.5px] border-saray-gold/60 rounded-2xl p-0 overflow-hidden shadow-lg bg-black/60">
            <button
              onClick={() => {
                markCampaignSeen(campaign.image);
                setShowCampaign(false);
              }}
              className="absolute top-3 right-3 text-white/80 hover:text-saray-gold"
              aria-label="Kapat"
            >
              <X size={20} />
            </button>
            <img src={campaign.image} alt="Kampanya" className="w-full h-full max-h-[88vh] object-contain bg-black" />
          </div>
        </div>
      )}

      {/* Sticky Header */}
      <div className="sticky top-0 z-30 bg-white/90 dark:bg-saray-black/90 backdrop-blur-xl border-b border-stone-200 dark:border-white/5 px-4 py-3 shadow-sm transition-colors duration-500">
        <div className="w-full max-w-3xl mx-auto flex flex-col gap-2 md:flex-row md:items-center md:justify-between md:gap-3">
          <div className="w-full flex flex-col gap-1.5 md:gap-2">
            <div className="w-full flex items-start justify-between md:items-center md:justify-start">
              <button
                className="flex flex-col items-start gap-0.5 cursor-pointer group shrink-0 leading-tight text-left"
                onClick={() => setSelectedCatId(null)}
                aria-label="Ana menüye dön"
              >
                <div className="font-serif font-bold text-saray-gold text-[12px] sm:text-sm tracking-[0.35em] group-hover:text-saray-gold/80 transition-colors">
                  SARAY
                </div>
                <div className="font-serif font-bold text-saray-gold text-[12px] sm:text-sm tracking-[0.35em] group-hover:text-saray-gold/80 transition-colors">
                  GÜLÜ
                </div>
              </button>

              <div className="flex items-center text-stone-600 dark:text-saray-gold gap-1 shrink-0 whitespace-nowrap">
                <a
                  href="https://www.instagram.com/saray_gulu/"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="p-2.5 rounded-full hover:bg-stone-100 dark:hover:bg-white/10 hover:text-saray-gold dark:hover:text-white transition-colors"
                  aria-label="Instagram"
                >
                  <Instagram size={20} />
                </a>

                <button
                  onClick={() => setShowWifi(true)}
                  className="p-2.5 rounded-full hover:bg-stone-100 dark:hover:bg-white/10 hover:text-saray-gold dark:hover:text-white transition-colors shrink-0"
                  aria-label="Wifi"
                >
                  <Wifi size={20} />
                </button>

                <div className="w-[1px] h-4 bg-stone-300 dark:bg-white/20 mx-1"></div>

                <ThemeToggle />
              </div>
            </div>

            <div className="w-full flex flex-col gap-1 md:flex-row md:items-center md:gap-3">
              <div className="w-full md:max-w-md">
                <input
                  type="text"
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  placeholder={translate('searchPlaceholder')}
                  className="w-full min-w-0 rounded-full border border-saray-gold/30 bg-white/80 dark:bg-white/10 px-4 py-2.5 text-sm text-stone-800 placeholder-stone-400 dark:text-white focus:outline-none focus:ring-2 focus:ring-saray-gold/50 focus:border-saray-gold/70"
                />
              </div>
              <div className="text-[10px] sm:text-xs uppercase tracking-[0.3em] text-saray-gold/70 md:ml-auto md:text-right">
                CAFE · PASTANE · RESTAURANT
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="max-w-md mx-auto p-4 z-10 relative">

        {apiError && (
          <div className="mb-4 rounded-lg border border-amber-400/60 bg-amber-50 text-amber-900 dark:bg-amber-950/40 dark:text-amber-100 px-3 py-2 text-sm">
            {apiError}
          </div>
        )}
        
        {/* Navigation */}
        {selectedCatId && (
            <button 
                onClick={() => setSelectedCatId(null)}
                className="mb-4 text-xs font-bold text-saray-gold hover:text-stone-800 dark:hover:text-white flex items-center gap-1 font-sans tracking-wide uppercase transition-colors"
            >
                ← {translate('categories')}
            </button>
        )}

        {!selectedCatId && !isSearching ? (
            /* Categories Grid */
            <div className="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-3 animate-in fade-in slide-in-from-bottom-4 duration-700">
                {isLoadingData && categories.length === 0 && (
                  <div className="col-span-full text-center text-saray-muted">Menü yükleniyor...</div>
                )}
                {!isLoadingData && categories.length === 0 && (
                  <div className="col-span-full text-center text-saray-muted">Henüz kategori eklenmemiş.</div>
                )}
                {categories.map((cat) => (
                    <motion.button
                        key={cat.id}
                        whileHover={{ scale: 1.02 }}
                        whileTap={{ scale: 0.98 }}
                        onClick={() => setSelectedCatId(cat.id)}
                        className="relative aspect-square rounded-2xl overflow-hidden shadow-lg group"
                    >
                        {cat.image ? (
                          <img src={cat.image} className="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt={cat.name[language]} />
                        ) : (
                          <div className="absolute inset-0 w-full h-full bg-gradient-to-br from-saray-black to-saray-olive" />
                        )}
                        <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-80 group-hover:opacity-90 transition-opacity"></div>
                        <div className="absolute bottom-0 left-0 right-0 p-4 text-center">
                            <span className="font-serif text-white text-lg font-bold drop-shadow-md border-b-2 border-saray-gold/0 group-hover:border-saray-gold transition-all pb-1">
                                {cat.name[language]}
                            </span>
                        </div>
                    </motion.button>
                ))}
            </div>
        ) : (
            /* Product List */
            <div className="space-y-4">
                <h2 className="font-serif text-2xl text-stone-800 dark:text-saray-gold mb-6 border-b border-stone-200 dark:border-white/10 pb-2">
                    {selectedCatId
                      ? categories.find(c => c.id === selectedCatId)?.name[language]
                      : translate('searchResults')}
                </h2>
                
                {displayedProducts.map((product, idx) => (
                  <motion.div
                    key={product.id}
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: idx * 0.05 }}
                    onClick={() => setSelectedProduct(product)}
                    className="bg-white dark:bg-saray-surface border border-stone-200 dark:border-white/5 rounded-xl overflow-hidden flex h-28 cursor-pointer group shadow-sm hover:shadow-md dark:shadow-none transition-all"
                  >
                    <div className="w-28 h-full relative shrink-0">
                       <img src={product.image} className="w-full h-full object-cover" alt="" />
                    </div>
                    
                    <div className="flex-1 p-3 flex flex-col justify-between">
                       <div>
                           <div className="flex justify-between items-start">
                                <h4 className="font-serif text-stone-900 dark:text-saray-text font-bold leading-tight text-sm">{product.name[language]}</h4>
                                {product.isPopular && <span className="text-[10px] text-saray-black bg-saray-gold px-1.5 rounded-sm font-bold">★</span>}
                           </div>
                           <p className="text-[10px] text-stone-500 dark:text-saray-muted mt-1 line-clamp-2">{product.description[language]}</p>
                       </div>
                       <div className="self-end">
                           <span className="font-serif text-saray-gold font-bold text-lg">{product.price} <span className="text-xs">{translate('currency')}</span></span>
                       </div>
                    </div>
                  </motion.div>
                ))}
                
                {displayedProducts.length === 0 && (
                    <div className="text-center text-stone-400 py-10 italic">Sonuç bulunamadı.</div>
                )}
            </div>
        )}

      </div>

      {/* Modals */}
      <FeedbackToggle onClick={() => setShowFeedback(true)} />
      <FeedbackModal isOpen={showFeedback} onClose={() => setShowFeedback(false)} branches={branches} />
      <ProductModal product={selectedProduct} onClose={() => setSelectedProduct(null)} />
      <WifiModal isOpen={showWifi} onClose={() => setShowWifi(false)} branches={branches} />
    </div>
  );
};

const AppContent = () => {
  const { isRTL } = useApp();
  
  return (
    <div className={`min-h-screen ${isRTL ? 'rtl' : 'ltr'}`}>
      <Routes>
        <Route path="/" element={<SplashScreen />} />
        <Route path="/menu" element={<MenuScreen />} />
      </Routes>
    </div>
  );
};

const App = () => {
  return (
    <AppProvider>
      <HashRouter>
        <AppContent />
      </HashRouter>
    </AppProvider>
  );
};

export default App;