import React from 'react';
import { 
  Users, 
  Activity, 
  TrendingUp, 
  CalendarCheck,
  MoreHorizontal,
  ArrowUpRight,
  ArrowDownRight
} from 'lucide-react';
import { 
  AreaChart, 
  Area, 
  XAxis, 
  YAxis, 
  CartesianGrid, 
  Tooltip, 
  ResponsiveContainer 
} from 'recharts';

const data = [
  { name: 'Mon', visits: 40 },
  { name: 'Tue', visits: 52 },
  { name: 'Wed', visits: 38 },
  { name: 'Thu', visits: 65 },
  { name: 'Fri', visits: 48 },
  { name: 'Sat', visits: 70 },
  { name: 'Sun', visits: 55 },
];

const StatCard = ({ title, value, trend, trendType, icon: Icon, color }: any) => (
  <div className="glass-card animate-fade-in">
    <div className="flex items-start justify-between mb-4">
      <div className={`p-3 rounded-2xl ${color} bg-opacity-20`}>
        <Icon className={`w-6 h-6 ${color.replace('bg-', 'text-')}`} />
      </div>
      <button className="p-1 text-slate-500 hover:text-white transition-colors">
        <MoreHorizontal className="w-5 h-5" />
      </button>
    </div>
    <div>
      <h3 className="text-slate-400 text-sm font-medium mb-1">{title}</h3>
      <div className="flex items-end gap-3">
        <span className="text-3xl font-bold font-outfit">{value}</span>
        <span className={`text-xs font-bold mb-1 flex items-center ${trendType === 'up' ? 'text-emerald-400' : 'text-rose-400'}`}>
          {trendType === 'up' ? <ArrowUpRight className="w-3 h-3 mr-0.5" /> : <ArrowDownRight className="w-3 h-3 mr-0.5" />}
          {trend}
        </span>
      </div>
    </div>
  </div>
);

export const Dashboard: React.FC = () => {
  return (
    <div className="space-y-8 pb-12">
      <div className="flex items-end justify-between">
        <div>
          <h1 className="text-4xl font-extrabold font-outfit tracking-tight mb-2">Hospital Overview</h1>
          <p className="text-slate-400">Welcome back, here's what's happening today.</p>
        </div>
        <div className="flex items-center gap-3">
          <select className="bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/50">
            <option>Last 7 Days</option>
            <option>Last 30 Days</option>
            <option>This Year</option>
          </select>
          <button className="bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2 px-6 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-95">
            Export Report
          </button>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard title="Total Patients" value="1,284" trend="+12.5%" trendType="up" icon={Users} color="bg-blue-500" />
        <StatCard title="Avg. Consultation" value="18m" trend="-2.4%" trendType="down" icon={Activity} color="bg-emerald-500" />
        <StatCard title="Today's Revenue" value="$4,520" trend="+8.2%" trendType="up" icon={TrendingUp} color="bg-indigo-500" />
        <StatCard title="Appointments" value="42" trend="+5.1%" trendType="up" icon={CalendarCheck} color="bg-amber-500" />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 glass-panel p-8">
          <div className="flex items-center justify-between mb-8">
            <div>
              <h2 className="text-xl font-bold font-outfit">Patient Traffic</h2>
              <p className="text-sm text-slate-400">Daily visitors across all departments</p>
            </div>
          </div>
          <div className="h-[300px] w-full">
            <ResponsiveContainer width="100%" height="100%">
              <AreaChart data={data}>
                <defs>
                  <linearGradient id="colorVisits" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="#10b981" stopOpacity={0.3}/>
                    <stop offset="95%" stopColor="#10b981" stopOpacity={0}/>
                  </linearGradient>
                </defs>
                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="rgba(255,255,255,0.05)" />
                <XAxis 
                  dataKey="name" 
                  axisLine={false} 
                  tickLine={false} 
                  tick={{ fill: '#64748b', fontSize: 12 }}
                  dy={10}
                />
                <YAxis 
                  axisLine={false} 
                  tickLine={false} 
                  tick={{ fill: '#64748b', fontSize: 12 }}
                />
                <Tooltip 
                  contentStyle={{ 
                    backgroundColor: '#1e293b', 
                    border: '1px solid rgba(255,255,255,0.1)',
                    borderRadius: '12px',
                    color: '#f8fafc'
                  }}
                />
                <Area 
                  type="monotone" 
                  dataKey="visits" 
                  stroke="#10b981" 
                  strokeWidth={3}
                  fillOpacity={1} 
                  fill="url(#colorVisits)" 
                />
              </AreaChart>
            </ResponsiveContainer>
          </div>
        </div>

        <div className="glass-panel p-8">
          <h2 className="text-xl font-bold font-outfit mb-6">Upcoming Appointments</h2>
          <div className="space-y-6">
            {[1, 2, 3, 4].map((i) => (
              <div key={i} className="flex items-center gap-4 group cursor-pointer">
                <div className="w-12 h-12 rounded-xl bg-white/5 border border-white/10 overflow-hidden flex-shrink-0">
                  <img src={`https://i.pravatar.cc/150?u=${i}`} alt="Patient" className="w-full h-full object-cover" />
                </div>
                <div className="flex-1 min-w-0">
                  <h4 className="text-sm font-bold text-white truncate group-hover:text-emerald-400 transition-colors">Patient Name {i}</h4>
                  <p className="text-xs text-slate-500">General Checkup • 09:30 AM</p>
                </div>
                <div className="px-2 py-1 rounded-md bg-white/5 text-[10px] font-bold text-slate-400 uppercase">
                  OPD
                </div>
              </div>
            ))}
          </div>
          <button className="w-full mt-8 py-3 bg-white/5 hover:bg-white/10 text-sm font-semibold rounded-xl border border-white/10 transition-all">
            View All Appointments
          </button>
        </div>
      </div>
    </div>
  );
};
