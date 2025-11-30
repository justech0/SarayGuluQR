import React from 'react';
import { motion } from 'framer-motion';
import { LOGO_URL } from '../constants';

type LogoProps = {
  className?: string;
  variant?: 'light' | 'dark';
  size?: 'lg' | 'sm';
};

export const Logo: React.FC<LogoProps> = ({ className, variant = 'light', size = 'lg' }) => {
  const height = size === 'lg' ? 'h-20 md:h-24' : 'h-12 md:h-14';
  const glowClass =
    variant === 'dark'
      ? 'drop-shadow-[0_8px_24px_rgba(212,175,55,0.2)]'
      : 'drop-shadow-[0_6px_18px_rgba(0,0,0,0.12)]';

  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.95 }}
      animate={{ opacity: 1, scale: 1 }}
      transition={{ duration: 0.8, ease: 'easeOut' }}
      className={`flex flex-col items-center justify-center text-center ${className ?? ''}`}
    >
      <img
        src={LOGO_URL}
        alt="Saray Gülü"
        className={`${height} w-auto object-contain ${glowClass}`}
        loading="lazy"
      />
    </motion.div>
  );
};