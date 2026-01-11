<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Date and Time Selection -->
        <div class="bg-white dark:bg-gray-900 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold mb-4">Attendance Settings</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Date</label>
                    <input 
                        type="date" 
                        wire:model="attendanceDate" 
                        wire:change="loadStudents"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Default Time</label>
                    <input 
                        type="time" 
                        wire:model="attendanceTime"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white"
                    >
                </div>
            </div>
        </div>

        <!-- Students Attendance Table -->
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Student Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($studentAttendance as $studentId => $student)
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-6 py-4 font-medium">{{ $student['name'] }}</td>
                            <td class="px-6 py-4">
                                <select 
                                    wire:model="studentAttendance.{{ $studentId }}.status"
                                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm"
                                >
                                    <option value="present">Present</option>
                                    <option value="late">Late</option>
                                    <option value="absent">Absent</option>
                                </select>
                            </td>
                            <td class="px-6 py-4">
                                <input 
                                    type="time" 
                                    wire:model="studentAttendance.{{ $studentId }}.time"
                                    @if($student['status'] === 'absent') disabled @endif
                                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm"
                                >
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                No students found. Please add students first.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end">
            <button 
                wire:click="saveAttendance"
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors"
            >
                Save Attendance
            </button>
        </div>
    </div>
</x-filament-panels::page>
