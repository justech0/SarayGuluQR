import React from 'react';
import { motion } from 'framer-motion';
import { LOGO_URL } from '../constants';

type LogoProps = {
  className?: string;
  variant?: 'light' | 'dark';
  size?: 'lg' | 'sm';
};

export const Logo: React.FC<LogoProps> = ({ className, variant = 'light', size = 'lg' }) => {
  const glowClass =
    variant === 'dark'
      ? 'shadow-[0_0_45px_rgba(212,175,55,0.25)]'
      : 'shadow-[0_0_35px_rgba(0,0,0,0.12)]';

  const frameBg =
    variant === 'dark'
      ? 'bg-gradient-to-b from-black/80 via-saray-black/80 to-black/60 border-saray-gold/40'
      : 'bg-gradient-to-b from-white via-amber-50 to-white border-saray-gold/60';

  const frameSize = size === 'lg' ? 'w-56 h-32' : 'w-24 h-14';

  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.95 }}
      animate={{ opacity: 1, scale: 1 }}
      transition={{ duration: 0.8, ease: 'easeOut' }}
      className={`flex flex-col items-center justify-center text-center ${className ?? ''}`}
    >
      <div
        className={`${frameSize} flex items-center justify-center border rounded-2xl overflow-hidden ${frameBg} ${glowClass}`}
      >
        <img
          src={LOGO_URL}
          alt="Saray Gülü"
          className="max-w-full max-h-full object-contain p-3"
          loading="lazy"
        />
      </div>
    </motion.div>
  );
};