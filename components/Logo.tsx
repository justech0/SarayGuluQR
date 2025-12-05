import React from 'react';
import { motion } from 'framer-motion';
import { LOGO_URL } from '../constants';

type LogoProps = {
  className?: string;
  variant?: 'light' | 'dark';
  size?: 'lg' | 'sm';
};

export const Logo: React.FC<LogoProps> = ({ className, variant = 'light', size = 'lg' }) => {
  const height = size === 'lg' ? 'h-40 md:h-48' : 'h-14 md:h-16';

  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.98 }}
      animate={{ opacity: 1, scale: 1 }}
      transition={{ duration: 0.9, ease: 'easeOut' }}
      className={`flex items-center justify-center text-center ${className ?? ''}`}
    >
      <img
        src={LOGO_URL || '/saray-gulu-logo-transparent-big.png'}
        alt="Saray Gülü"
        className={`${height} max-h-64 w-auto object-contain`}
        loading="lazy"
      />
    </motion.div>
  );
};