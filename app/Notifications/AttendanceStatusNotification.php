<?php

namespace App\Notifications;

use App\Models\AttendanceRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttendanceStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public AttendanceRecord $record)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $studentName = $this->record->student->first_name.' '.$this->record->student->last_name;
        $subjectName = $this->record->subject->name ?? 'N/A';
        $status = ucfirst($this->record->status);
        $time = $this->record->timestamp->format('h:i A');
        $date = $this->record->timestamp->format('M d, Y');

        return (new MailMessage)
            ->subject("Attendance Notification: {$studentName} - {$status}")
            ->greeting('Hello!')
            ->line("This is an automated notification regarding the attendance of {$studentName}.")
            ->line("Subject: {$subjectName}")
            ->line("Status: {$status}")
            ->line("Date: {$date}")
            ->line("Time: {$time}")
            ->action('View Attendance Dashboard', url('/dashboard/attendance'))
            ->line('Thank you for using Koattendance!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
