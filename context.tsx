import React, { createContext, useContext, useState, ReactNode, useEffect } from 'react';
import { Language, Theme } from './types';
import { TRANSLATIONS } from './constants';

interface AppContextType {
  language: Language;
  setLanguage: (lang: Language) => void;
  translate: (key: keyof typeof TRANSLATIONS['tr']) => string;
  isRTL: boolean;
  theme: Theme;
  toggleTheme: () => void;
}

const AppContext = createContext<AppContextType | undefined>(undefined);

export const AppProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [language, setLanguage] = useState<Language>('tr');
  const [theme, setTheme] = useState<Theme>('dark');

  const isRTL = language === 'ar';

  useEffect(() => {
    document.documentElement.dir = isRTL ? 'rtl' : 'ltr';
    document.documentElement.lang = language;
  }, [language, isRTL]);

  useEffect(() => {
    if (theme === 'dark') {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  }, [theme]);

  const toggleTheme = () => {
    setTheme(prev => prev === 'dark' ? 'light' : 'dark');
  };

  const translate = (key: keyof typeof TRANSLATIONS['tr']) => {
    return TRANSLATIONS[language][key];
  };

  return (
    <AppContext.Provider value={{ language, setLanguage, translate, isRTL, theme, toggleTheme }}>
      {children}
    </AppContext.Provider>
  );
};

export const useApp = () => {
  const context = useContext(AppContext);
  if (!context) throw new Error("useApp must be used within AppProvider");
  return context;
};