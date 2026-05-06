import React from 'react';
import { 
  Calendar, 
  Clock, 
  User, 
  CheckCircle2, 
  Timer,
  ChevronRight,
  Plus
} from 'lucide-react';

const appointments = [
  { id: 'AP-2041', patient: 'Sarah Miller', type: 'General Checkup', time: '09:00 AM', status: 'In-Progress', doctor: 'Dr. Johnson' },
  { id: 'AP-2042', patient: 'David Chen', type: 'Cardiology', time: '10:30 AM', status: 'Scheduled', doctor: 'Dr. Smith' },
  { id: 'AP-2043', patient: 'Emma Watson', type: 'Dermatology', time: '11:15 AM', status: 'Scheduled', doctor: 'Dr. Sarah' },
  { id: 'AP-2044', patient: 'Robert Gale', type: 'Follow-up', time: '02:00 PM', status: 'Pending', doctor: 'Dr. Johnson' },
];

export const Appointments: React.FC = () => {
  return (
    <div className="space-y-8 animate-fade-in">
      <div className="flex items-end justify-between">
        <div>
          <h1 className="text-4xl font-extrabold font-outfit tracking-tight mb-2">Appointments</h1>
          <p className="text-slate-400">Manage daily tokens and scheduled consultations.</p>
        </div>
        <div className="flex gap-4">
          <div className="flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-sm font-medium">
            <Calendar className="w-4 h-4 text-emerald-400" />
            <span>Today, May 1st</span>
          </div>
          <button className="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 px-6 rounded-2xl transition-all shadow-lg shadow-emerald-500/20">
            <Plus className="w-5 h-5" />
            Book Appointment
          </button>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div className="lg:col-span-3 space-y-6">
          <div className="glass-panel p-6">
            <div className="flex items-center justify-between mb-8">
              <h2 className="text-xl font-bold font-outfit">Active Queue</h2>
              <div className="flex gap-2">
                <span className="px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-xs font-bold uppercase tracking-wider">
                  Live
                </span>
              </div>
            </div>

            <div className="space-y-4">
              {appointments.map((apt) => (
                <div key={apt.id} className="group glass-card hover:bg-white/5 transition-all flex items-center justify-between p-5">
                  <div className="flex items-center gap-6">
                    <div className="w-16 h-16 rounded-2xl bg-white/5 border border-white/10 flex flex-col items-center justify-center text-center">
                      <span className="text-xs text-slate-500 uppercase font-bold">Token</span>
                      <span className="text-xl font-black font-outfit text-emerald-400">#{apt.id.split('-')[1]}</span>
                    </div>
                    <div>
                      <h4 className="text-lg font-bold text-white mb-1 group-hover:text-emerald-400 transition-colors">{apt.patient}</h4>
                      <div className="flex items-center gap-3 text-sm text-slate-400">
                        <span className="flex items-center gap-1"><Clock className="w-3 h-3" /> {apt.time}</span>
                        <span className="w-1 h-1 rounded-full bg-slate-700" />
                        <span>{apt.type}</span>
                      </div>
                    </div>
                  </div>

                  <div className="flex items-center gap-8">
                    <div className="hidden md:block text-right">
                      <p className="text-xs text-slate-500 uppercase font-bold mb-1">Assigned To</p>
                      <p className="text-sm font-semibold text-white">{apt.doctor}</p>
                    </div>
                    <div className="flex items-center gap-4">
                      <span className={`px-3 py-1.5 rounded-lg text-xs font-bold ${
                        apt.status === 'In-Progress' ? 'bg-indigo-500/10 text-indigo-400' :
                        apt.status === 'Scheduled' ? 'bg-emerald-500/10 text-emerald-400' :
                        'bg-amber-500/10 text-amber-400'
                      }`}>
                        {apt.status}
                      </span>
                      <button className="p-2 hover:bg-white/10 rounded-xl transition-colors">
                        <ChevronRight className="w-5 h-5 text-slate-500" />
                      </button>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        <div className="space-y-6">
          <div className="glass-panel p-6">
            <h3 className="text-lg font-bold font-outfit mb-6">Daily Stats</h3>
            <div className="space-y-6">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <div className="p-2 rounded-lg bg-emerald-500/10">
                    <CheckCircle2 className="w-4 h-4 text-emerald-400" />
                  </div>
                  <span className="text-sm text-slate-400">Completed</span>
                </div>
                <span className="font-bold">24</span>
              </div>
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <div className="p-2 rounded-lg bg-amber-500/10">
                    <Timer className="w-4 h-4 text-amber-400" />
                  </div>
                  <span className="text-sm text-slate-400">Waiting</span>
                </div>
                <span className="font-bold">12</span>
              </div>
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <div className="p-2 rounded-lg bg-indigo-500/10">
                    <User className="w-4 h-4 text-indigo-400" />
                  </div>
                  <span className="text-sm text-slate-400">Total Tokens</span>
                </div>
                <span className="font-bold">42</span>
              </div>
            </div>
            
            <div className="mt-8 pt-8 border-t border-white/5">
              <p className="text-xs text-slate-500 font-bold uppercase tracking-widest mb-4">Live Capacity</p>
              <div className="h-2 w-full bg-white/5 rounded-full overflow-hidden">
                <div className="h-full bg-emerald-500 rounded-full" style={{ width: '65%' }}></div>
              </div>
              <p className="text-[10px] text-slate-500 mt-2">65% of daily capacity reached</p>
            </div>
          </div>

          <div className="glass-panel p-6 bg-emerald-500/5 border-emerald-500/10">
            <h3 className="text-sm font-bold text-emerald-400 uppercase tracking-widest mb-4">Quick Actions</h3>
            <button className="w-full py-3 px-4 bg-white/5 hover:bg-white/10 rounded-xl text-sm font-semibold transition-all mb-3 text-left">
              Generate Today's Token
            </button>
            <button className="w-full py-3 px-4 bg-white/5 hover:bg-white/10 rounded-xl text-sm font-semibold transition-all text-left">
              View Doctor's Schedule
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
