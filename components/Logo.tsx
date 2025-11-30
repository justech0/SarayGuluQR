import React from 'react';
import { motion } from 'framer-motion';
import { LOGO_URL } from '../constants';

type LogoProps = {
  className?: string;
  variant?: 'light' | 'dark';
  size?: 'lg' | 'sm';
};

export const Logo: React.FC<LogoProps> = ({ className, variant = 'light', size = 'lg' }) => {
  const height = size === 'lg' ? 'h-14 md:h-16' : 'h-9 md:h-10';

  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.98 }}
      animate={{ opacity: 1, scale: 1 }}
      transition={{ duration: 0.9, ease: 'easeOut' }}
      className={`flex items-center justify-center text-center ${className ?? ''}`}
    >
      <img
        src={LOGO_URL}
        alt="Saray Gülü"
        className={`${height} max-h-24 w-auto object-contain`}
        loading="lazy"
      />
    </motion.div>
  );
};