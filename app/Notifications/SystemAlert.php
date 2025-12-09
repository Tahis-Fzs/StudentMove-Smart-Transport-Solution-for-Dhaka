  public function __construct($message, $type = 'system')
    {
        $this->message = $message;
        $this->type = $type;
    }

    // FR-27: Define Channels (Send via Email AND Database)
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    // 1. Define Email Layout
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('StudentMove Alert: ' . ucfirst($this->type))
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line($this->message)
                    ->action('View Dashboard', url('/dashboard'))
                    ->line('Thank you for using our service!');
    }

    // 2. Define App/Database Layout
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
            'icon' => 'bi-bell-fill', // Default icon
            'icon_color' => 'bg-primary'
        ];
    }
}