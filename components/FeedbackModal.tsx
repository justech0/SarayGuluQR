import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { X, MessageSquare, ChevronRight, Star, Send } from 'lucide-react';
import { useApp } from '../context';
import { BRANCHES } from '../constants';

export const FeedbackToggle: React.FC<{ onClick: () => void }> = ({ onClick }) => {
  const { translate } = useApp();
  return (
    <motion.button
      initial={{ scale: 0 }}
      animate={{ scale: 1 }}
      whileHover={{ scale: 1.05 }}
      onClick={onClick}
      className="fixed bottom-6 right-6 z-40 bg-white dark:bg-saray-surface border border-saray-gold/30 rounded-lg shadow-2xl overflow-hidden group flex items-center pr-4 pl-3 py-3"
    >
      <div className="bg-saray-gold/10 p-2 rounded-full mr-3 text-saray-gold">
         <MessageSquare size={20} />
      </div>
      <div className="flex flex-col items-start">
        <span className="text-[10px] text-stone-500 dark:text-saray-muted uppercase tracking-widest leading-none mb-1">Bize Yazın</span>
        <span className="text-sm font-serif font-bold text-stone-900 dark:text-saray-text leading-none">{translate('feedbackTitle')}</span>
      </div>
    </motion.button>
  );
};

export const FeedbackModal: React.FC<{ isOpen: boolean; onClose: () => void }> = ({ isOpen, onClose }) => {
  const { translate } = useApp();
  const [step, setStep] = useState<0 | 1 | 2 | 3>(0); // 0: Branch, 1: Topic, 2: Rating, 3: Form
  const [branch, setBranch] = useState<string>('');
  const [topic, setTopic] = useState<string>('');
  const [rating, setRating] = useState<number | null>(null);
  const [comment, setComment] = useState('');
  const [isRecoveryMode, setIsRecoveryMode] = useState(false);
  const [contact, setContact] = useState('');

  const topics = [
    { id: 'taste', label: 'LEZZET VE KALİTE' },
    { id: 'service', label: 'HİZMET HIZINIZ' },
    { id: 'staff', label: 'PERSONEL' },
    { id: 'hygiene', label: 'HİJYEN' },
    { id: 'other', label: 'DİĞER (GÖRÜŞ ÖNERİ)' },
  ];

  const handleBranchSelect = (bId: string) => {
    setBranch(bId);
    setStep(1);
  };

  const handleTopicSelect = (t: string) => {
    setTopic(t);
    setStep(2);
  };

  const handleRating = (r: number) => {
    setRating(r);
    if (r <= 2) {
      setIsRecoveryMode(true);
    } else {
      setIsRecoveryMode(false);
    }
    setTimeout(() => setStep(3), 400);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    console.log({ branch, topic, rating, comment, contact });
    
    // Simulate API call
    setTimeout(() => {
        setStep(0);
        setBranch('');
        setTopic('');
        setRating(null);
        setComment('');
        onClose();
        alert(translate('feedbackSuccess'));
    }, 1000);
  };

  if (!isOpen) return null;

  return (
    <AnimatePresence>
      <div className="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4">
        <motion.div 
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          onClick={onClose}
          className="absolute inset-0 bg-black/80 backdrop-blur-sm"
        />
        
        <motion.div 
          initial={{ y: "100%" }}
          animate={{ y: 0 }}
          exit={{ y: "100%" }}
          className="relative w-full max-w-md bg-white dark:bg-saray-surface border-t-2 sm:border-2 border-saray-gold/20 rounded-t-2xl sm:rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
        >
          {/* Header */}
          <div className="bg-stone-100 dark:bg-saray-black/50 p-4 border-b border-black/5 dark:border-white/5 flex justify-between items-center sticky top-0 z-10">
            <h3 className="font-serif text-lg text-saray-gold">{translate('feedbackTitle')}</h3>
            <button onClick={onClose} className="text-stone-500 dark:text-saray-muted hover:text-black dark:hover:text-white"><X size={20} /></button>
          </div>

          <div className="p-6 overflow-y-auto bg-stone-50 dark:bg-transparent custom-scrollbar">
            
            {/* Step 0: Branch Selection */}
            {step === 0 && (
              <motion.div initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }}>
                <p className="text-center text-stone-700 dark:text-saray-text mb-6 font-sans font-bold">{translate('selectBranch')}</p>
                <div className="space-y-3">
                  {BRANCHES.map((b) => (
                    <button
                      key={b.id}
                      onClick={() => handleBranchSelect(b.id)}
                      className="w-full p-4 rounded-xl bg-white dark:bg-white/5 border border-stone-200 dark:border-white/10 hover:border-saray-gold hover:bg-saray-gold/5 transition-all text-left flex justify-between items-center group shadow-sm"
                    >
                      <span className="font-serif font-bold text-stone-800 dark:text-saray-text">{b.name}</span>
                      <ChevronRight size={18} className="text-stone-400 group-hover:text-saray-gold" />
                    </button>
                  ))}
                </div>
              </motion.div>
            )}

            {/* Step 1: Topic Selection */}
            {step === 1 && (
              <motion.div initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }}>
                <p className="text-center text-stone-700 dark:text-saray-text mb-6 font-sans">{translate('selectTopic')}</p>
                <div className="space-y-3">
                  {topics.map((t) => (
                    <button
                      key={t.id}
                      onClick={() => handleTopicSelect(t.id)}
                      className="w-full p-4 rounded-xl bg-saray-olive text-white font-bold hover:bg-opacity-90 transition-all text-center shadow-md border border-white/10"
                    >
                      {t.label}
                    </button>
                  ))}
                </div>
                <button onClick={() => setStep(0)} className="mt-4 w-full text-center text-xs text-stone-400 underline py-2">
                    Geri Dön
                </button>
              </motion.div>
            )}

            {/* Step 2: Rating */}
            {step === 2 && (
              <motion.div initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} className="text-center py-8">
                <p className="text-lg font-serif text-stone-800 dark:text-saray-text mb-8">{translate('rateUs')}</p>
                <div className="flex justify-center gap-2 mb-8">
                  {[1, 2, 3, 4, 5].map((r) => (
                    <button 
                      key={r}
                      onClick={() => handleRating(r)}
                      className="p-2 hover:scale-110 transition-transform"
                    >
                      <Star 
                        size={36} 
                        fill={rating && rating >= r ? "#D4AF37" : "transparent"} 
                        className={rating && rating >= r ? "text-saray-gold" : "text-stone-300 dark:text-stone-600"}
                      />
                    </button>
                  ))}
                </div>
                 <button onClick={() => setStep(1)} className="mt-4 w-full text-center text-xs text-stone-400 underline py-2">
                    Geri Dön
                </button>
              </motion.div>
            )}

            {/* Step 3: Message Form */}
            {step === 3 && (
              <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
                {isRecoveryMode && (
                  <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/50 p-4 rounded-xl mb-6 flex items-start gap-3">
                    <div className="p-2 bg-red-100 dark:bg-red-900/40 rounded-full text-red-600 dark:text-red-400">
                         <MessageSquare size={16} />
                    </div>
                    <div>
                        <p className="font-bold text-red-800 dark:text-red-300 text-sm mb-1">Üzgünüz...</p>
                        <p className="text-xs text-red-700 dark:text-red-200">{translate('recoveryMessage')}</p>
                    </div>
                  </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-4">
                  <textarea
                    rows={4}
                    value={comment}
                    onChange={(e) => setComment(e.target.value)}
                    placeholder={translate('yourMessage')}
                    className="w-full p-4 rounded-xl bg-white dark:bg-black/20 border border-stone-200 dark:border-white/10 text-stone-800 dark:text-saray-text placeholder-stone-400 focus:border-saray-gold outline-none resize-none"
                  ></textarea>
                  
                  <input
                    type="text"
                    value={contact}
                    onChange={(e) => setContact(e.target.value)}
                    placeholder={translate('contactDetails')}
                    className="w-full p-4 rounded-xl bg-white dark:bg-black/20 border border-stone-200 dark:border-white/10 text-stone-800 dark:text-saray-text placeholder-stone-400 focus:border-saray-gold outline-none"
                  />

                  <button 
                    type="submit"
                    className="w-full py-4 bg-saray-gold text-saray-black font-bold rounded-xl shadow-lg hover:bg-saray-darkGold transition-colors flex items-center justify-center gap-2"
                  >
                    <span>{translate('submit')}</span>
                    <Send size={18} />
                  </button>
                </form>
              </motion.div>
            )}
            
          </div>
        </motion.div>
      </div>
    </AnimatePresence>
  );
};