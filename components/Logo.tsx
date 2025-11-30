import React from 'react';
import { motion } from 'framer-motion';
import { LOGO_URL } from '../constants';

export const Logo: React.FC<{ className?: string; variant?: 'light' | 'dark' }> = ({ className, variant = 'light' }) => {
  const glowClass =
    variant === 'dark'
      ? 'shadow-[0_0_45px_rgba(212,175,55,0.25)]'
      : 'shadow-[0_0_35px_rgba(0,0,0,0.2)]';

  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.95 }}
      animate={{ opacity: 1, scale: 1 }}
      transition={{ duration: 0.8, ease: 'easeOut' }}
      className={`flex flex-col items-center justify-center text-center w-full ${className}`}
    >
      <div
        className={`w-full max-w-xs bg-gradient-to-b from-black/80 via-saray-black/80 to-black/60 border border-saray-gold/40 rounded-2xl overflow-hidden ${glowClass}`}
      >
        <img
          src={LOGO_URL}
          alt="Saray Gülü"
          className="w-full h-full object-contain p-4 bg-black/40"
          loading="lazy"
        />
      </div>
    </motion.div>
  );
};