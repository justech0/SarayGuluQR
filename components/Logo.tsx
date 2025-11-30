import React from 'react';
import { motion } from 'framer-motion';
import { LOGO_URL } from '../constants';

type LogoProps = {
  className?: string;
  variant?: 'light' | 'dark';
  size?: 'lg' | 'sm';
};

export const Logo: React.FC<LogoProps> = ({ className, variant = 'light', size = 'lg' }) => {
  const height = size === 'lg' ? 'h-16 md:h-20' : 'h-10 md:h-12';

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
        className={`${height} w-auto object-contain`}
        loading="lazy"
      />
    </motion.div>
  );
};