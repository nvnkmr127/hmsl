import React from 'react';
import { NavLink } from 'react-router-dom';
import { 
  LayoutDashboard, 
  Users, 
  Calendar, 
  ClipboardPlus, 
  Stethoscope, 
  Pill, 
  Microscope, 
  Settings
} from 'lucide-react';

const navItems = [
  { icon: LayoutDashboard, label: 'Dashboard', path: '/' },
  { icon: Users, label: 'Patients', path: '/patients' },
  { icon: Calendar, label: 'Appointments', path: '/appointments' },
  { icon: ClipboardPlus, label: 'OPD Billing', path: '/opd-billing' },
  { icon: Stethoscope, label: 'Consultations', path: '/consultations' },
  { icon: Pill, label: 'Pharmacy', path: '/pharmacy' },
  { icon: Microscope, label: 'Laboratory', path: '/lab' },
  { icon: Settings, label: 'Settings', path: '/settings' },
];

export const Sidebar: React.FC = () => {
  return (
    <aside className="w-[280px] h-screen bg-[#0f172a]/50 backdrop-blur-xl border-r border-white/10 flex flex-col fixed left-0 top-0 z-50">
      <div className="p-8">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20">
            <Stethoscope className="text-white w-6 h-6" />
          </div>
          <span className="text-xl font-bold font-outfit tracking-tight">MedFlow <span className="text-emerald-500">HMS</span></span>
        </div>
      </div>

      <nav className="flex-1 px-4 space-y-2 mt-4">
        {navItems.map((item) => (
          <NavLink
            key={item.path}
            to={item.path}
            className={({ isActive }) => 
              `w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 group hover:bg-white/5 ${
                isActive ? 'bg-white/10 text-emerald-400' : 'text-slate-400 hover:text-white'
              }`
            }
          >
            {({ isActive }) => (
              <>
                <div className="flex items-center gap-3">
                  <item.icon className={`w-5 h-5 ${isActive ? 'text-emerald-400' : 'group-hover:text-white'}`} />
                  <span className="font-medium">{item.label}</span>
                </div>
                {isActive && <div className="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.6)]" />}
              </>
            )}
          </NavLink>
        ))}
      </nav>

      <div className="p-6 mt-auto">
        <div className="glass-card bg-emerald-500/10 border-emerald-500/20 p-4 rounded-2xl">
          <p className="text-sm font-medium text-emerald-400 mb-1">Doctor On Duty</p>
          <p className="text-xs text-slate-400 mb-3">Dr. Sarah Johnson</p>
          <button className="w-full py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-lg transition-colors">
            Switch Shift
          </button>
        </div>
      </div>
    </aside>
  );
};
