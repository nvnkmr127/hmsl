import React from 'react';
import { 
  Search, 
  Plus, 
  Filter, 
  MoreVertical,
  UserPlus,
  ArrowRight
} from 'lucide-react';

const patients = [
  { id: 'PT-001', name: 'John Doe', age: 45, gender: 'Male', phone: '+1 234 567 890', lastVisit: '2024-04-28', status: 'Follow-up' },
  { id: 'PT-002', name: 'Jane Smith', age: 32, gender: 'Female', phone: '+1 234 567 891', lastVisit: '2024-04-29', status: 'New' },
  { id: 'PT-003', name: 'Robert Brown', age: 58, gender: 'Male', phone: '+1 234 567 892', lastVisit: '2024-04-25', status: 'Stable' },
  { id: 'PT-004', name: 'Emily Davis', age: 24, gender: 'Female', phone: '+1 234 567 893', lastVisit: '2024-04-30', status: 'Critical' },
  { id: 'PT-005', name: 'Michael Wilson', age: 41, gender: 'Male', phone: '+1 234 567 894', lastVisit: '2024-04-27', status: 'Stable' },
];

export const Patients: React.FC = () => {
  return (
    <div className="space-y-8 animate-fade-in">
      <div className="flex items-end justify-between">
        <div>
          <h1 className="text-4xl font-extrabold font-outfit tracking-tight mb-2">Patient Directory</h1>
          <p className="text-slate-400">Manage and track all patient records from a central hub.</p>
        </div>
        <button className="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 px-6 rounded-2xl transition-all shadow-lg shadow-emerald-500/20 active:scale-95">
          <UserPlus className="w-5 h-5" />
          Register New Patient
        </button>
      </div>

      <div className="glass-panel p-6">
        <div className="flex flex-col md:flex-row items-center justify-between gap-4 mb-8">
          <div className="relative flex-1 max-w-md w-full">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
            <input 
              type="text" 
              placeholder="Search by name, ID, or phone..."
              className="w-full bg-white/5 border border-white/10 rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition-all"
            />
          </div>
          <div className="flex items-center gap-3">
            <button className="flex items-center gap-2 px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm font-medium hover:bg-white/10 transition-all">
              <Filter className="w-4 h-4" />
              Filters
            </button>
            <select className="bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm font-medium focus:outline-none">
              <option>Recently Added</option>
              <option>Name (A-Z)</option>
              <option>Critical First</option>
            </select>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="border-b border-white/5 text-slate-500 text-sm font-medium">
                <th className="px-4 py-4">Patient ID</th>
                <th className="px-4 py-4">Patient Name</th>
                <th className="px-4 py-4">Age / Gender</th>
                <th className="px-4 py-4">Phone Number</th>
                <th className="px-4 py-4">Last Visit</th>
                <th className="px-4 py-4">Status</th>
                <th className="px-4 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-white/5">
              {patients.map((patient) => (
                <tr key={patient.id} className="group hover:bg-white/[0.02] transition-colors">
                  <td className="px-4 py-4 text-sm font-mono text-emerald-400">{patient.id}</td>
                  <td className="px-4 py-4">
                    <div className="flex items-center gap-3">
                      <div className="w-8 h-8 rounded-lg bg-slate-800 border border-white/10 flex items-center justify-center text-xs font-bold text-slate-400">
                        {patient.name.charAt(0)}
                      </div>
                      <span className="font-semibold text-white group-hover:text-emerald-400 transition-colors">{patient.name}</span>
                    </div>
                  </td>
                  <td className="px-4 py-4 text-sm text-slate-400">{patient.age} / {patient.gender}</td>
                  <td className="px-4 py-4 text-sm text-slate-400">{patient.phone}</td>
                  <td className="px-4 py-4 text-sm text-slate-400">{patient.lastVisit}</td>
                  <td className="px-4 py-4">
                    <span className={`px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider ${
                      patient.status === 'Critical' ? 'bg-rose-500/10 text-rose-500' :
                      patient.status === 'New' ? 'bg-blue-500/10 text-blue-500' :
                      'bg-emerald-500/10 text-emerald-500'
                    }`}>
                      {patient.status}
                    </span>
                  </td>
                  <td className="px-4 py-4 text-right">
                    <div className="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                      <button className="p-2 hover:bg-emerald-500/10 text-emerald-400 rounded-lg transition-colors">
                        <ArrowRight className="w-4 h-4" />
                      </button>
                      <button className="p-2 hover:bg-white/5 text-slate-500 rounded-lg transition-colors">
                        <MoreVertical className="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        
        <div className="flex items-center justify-between mt-8 text-sm text-slate-500">
          <p>Showing 5 of 1,284 patients</p>
          <div className="flex items-center gap-2">
            <button className="px-4 py-2 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 disabled:opacity-50">Previous</button>
            <button className="px-4 py-2 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10">Next</button>
          </div>
        </div>
      </div>
    </div>
  );
};
