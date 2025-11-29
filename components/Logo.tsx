import React from 'react';
import { motion } from 'framer-motion';

export const Logo: React.FC<{ className?: string, variant?: 'light' | 'dark' }> = ({ className, variant = 'light' }) => {
  const iconColor = '#D4AF37';

  return (
    <motion.div 
      initial={{ opacity: 0, scale: 0.95 }}
      animate={{ opacity: 1, scale: 1 }}
      transition={{ duration: 0.8, ease: "easeOut" }}
      className={`flex flex-col items-center justify-center text-center w-full ${className}`}
    >
      {/* Rose Abstract Icon */}
      <div className="mb-3 relative drop-shadow-md">
         <svg width="80" height="80" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="48" stroke={iconColor} strokeWidth="1.5" />
            <circle cx="50" cy="50" r="44" stroke={iconColor} strokeWidth="0.5" strokeOpacity="0.5" />
            <path d="M50 25C65 25 75 35 75 50C75 65 65 75 50 75C35 75 25 65 25 50C25 35 35 25 50 25" stroke={iconColor} strokeWidth="2" strokeLinecap="round" />
            <path d="M50 35C40 35 35 40 35 50C35 60 40 65 50 65" stroke={iconColor} strokeWidth="2" strokeLinecap="round" />
            <path d="M50 45C53 45 55 47 55 50" stroke={iconColor} strokeWidth="2" strokeLinecap="round" />
         </svg>
      </div>

      <h1 className={`font-serif text-3xl sm:text-4xl font-bold tracking-widest text-saray-gold uppercase drop-shadow-sm`}>
        Saray Gülü
      </h1>
      <div className="flex items-center justify-center gap-3 mt-2 opacity-90 w-full">
        <div className="h-[1px] w-4 sm:w-8 bg-saray-gold/50"></div>
        <p className={`font-sans text-[10px] sm:text-[11px] font-bold tracking-[0.2em] dark:text-saray-muted text-stone-600 uppercase whitespace-nowrap`}>
          Cafe & Pastane & Restaurant
        </p>
        <div className="h-[1px] w-4 sm:w-8 bg-saray-gold/50"></div>
      </div>
    </motion.div>
  );
};