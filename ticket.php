<?php

include __DIR__ . '/vendor/autoload.php';

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Discord\Builders\MessageBuilder;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Guild\Role;
use Discord\Parts\Permissions\ChannelPermission;
use Discord\Parts\Channel\Overwrite;


$discord = new Discord([
    'token' => '',
]);

$discord->on('ready', function (Discord $discord) {
    echo "BOT TA ONLINEEE!", PHP_EOL;

    // Listen for messages
    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
        $ticketId = rand(1398, 5302);
        if ($message->content == '.ticket') { // 1
            $date = new DateTime();
            $date = "<t:".$date->getTimestamp().":R>";
            $builder = MessageBuilder::new();
            $builder->setContent("$date\n**Tickets Menu**");

            $actionRow = ActionRow::new();
            $buttonSuccess = Button::new(Button::STYLE_SECONDARY);
            $buttonSuccess->setLabel('Open Ticket');
            $actionRow->addComponent($buttonSuccess);
            $builder->addComponent($actionRow);

            $message->channel->sendMessage($builder);

            $buttonSuccess->setListener(function ($interaction) use ($discord, $message, $ticketId) {
                $interaction->user->sendMessage(MessageBuilder::new()->setContent("Ticket opened"));
                $guild = $interaction->guild;


                $permission = new ChannelPermission($discord, [
                    'view_channel' => true,
                ]);

                $newchannel = $interaction->guild->channels->create([
                    'name' => 'ticket-' . $ticketId,
                    'type' => Channel::TYPE_TEXT,
                    'topic' => 'Ticket Opened',
                    'permission_overwrites' => [
                        [
                            'id' => $interaction->guild->id, // @everyone role id
                            'type' => Overwrite::TYPE_ROLE,
                            'allow' => '0',
                            'deny' => $permission->bitwise,
                        ],
                        [
                            'id' => $interaction->user->id,
                            'type' => Overwrite::TYPE_MEMBER,
                            'allow' => $permission->bitwise,
                            'deny' => '0',
                        ]
                    ]
                ]);

                $interaction->guild->channels->save($newchannel)->done(function (Channel $channel) {
                    echo 'Created a new text channel - ID: ', $channel->id;
                });
            }, $discord);
        }
        if ($message->content == ".delete") {
            $channel = $message->channel;
            $guild = $message->guild;
            $builder = MessageBuilder::new();
            $builder->setContent('Tickets Menu');
            $actionRow = ActionRow::new();
            $buttonDelete = Button::new(Button::STYLE_DANGER);
            $buttonDelete->setLabel('Close Ticket');
            $actionRow->addComponent($buttonDelete);
            $builder->addComponent($actionRow);
            $channel->sendMessage($builder);
            $buttonDelete->setListener(function ($interactionDelete) use ($discord, $guild) {
                $guild->channels->delete($interactionDelete->channel_id);
            }, $discord);
        }
    });
});

$discord->run();
