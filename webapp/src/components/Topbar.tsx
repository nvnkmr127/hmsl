import React from 'react';
import { Search, Bell, User, Sun, Moon } from 'lucide-react';

export const Topbar: React.FC = () => {
  return (
    <header className="h-[72px] bg-[#0f172a]/20 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 sticky top-0 z-40 ml-[280px]">
      <div className="flex items-center gap-4 flex-1 max-w-xl">
        <div className="relative w-full group">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-focus-within:text-emerald-400 transition-colors" />
          <input 
            type="text" 
            placeholder="Search patients, records, medicines... (Alt+S)"
            className="w-full bg-white/5 border border-white/10 rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all placeholder:text-slate-600"
          />
        </div>
      </div>

      <div className="flex items-center gap-6">
        <div className="flex items-center gap-2">
          <button className="p-2.5 rounded-xl hover:bg-white/5 text-slate-400 transition-all relative">
            <Bell className="w-5 h-5" />
            <span className="absolute top-2 right-2 w-2 h-2 bg-emerald-500 rounded-full border-2 border-[#0f172a]" />
          </button>
          <button className="p-2.5 rounded-xl hover:bg-white/5 text-slate-400 transition-all">
            <Moon className="w-5 h-5" />
          </button>
        </div>

        <div className="h-8 w-[1px] bg-white/10 mx-2" />

        <button className="flex items-center gap-3 pl-2 pr-1 py-1 rounded-2xl hover:bg-white/5 transition-all group">
          <div className="text-right">
            <p className="text-sm font-semibold text-white group-hover:text-emerald-400 transition-colors">Admin User</p>
            <p className="text-[10px] text-slate-500 font-medium uppercase tracking-wider">Hospital Admin</p>
          </div>
          <div className="w-10 h-10 rounded-xl bg-slate-800 border border-white/10 flex items-center justify-center text-slate-300 font-bold overflow-hidden">
            <img 
              src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&q=80&w=100&h=100" 
              alt="Avatar"
              className="w-full h-full object-cover"
            />
          </div>
        </button>
      </div>
    </header>
  );
};
