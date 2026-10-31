<?php

namespace App\Demo;

/**
 * The demo world's cast (ADR-030): 20 fictional venues across Malaysia, the fictional players who book
 * them, and names for walk-ins. Every venue name ends in "(Demo)" and every page about it says it is
 * made up. Players use @demo.example.com addresses, which can never receive email.
 */
final class DemoVenues
{
    /** Players' addresses: example.com is reserved for examples and never delivers mail. */
    public const PLAYER_DOMAIN = 'demo.example.com';

    /**
     * Each venue: where it is, its courts [name, sport, indoor, price per hour in sen], its opening hours
     * ("standard" 8 am to midnight, "late" until 1 am on Friday and Saturday, "friday_break" with a Friday
     * prayer break, "early" 7 am to 11 pm), how busy it is (share of evening slots booked), and whether it
     * takes online payment (one venue, with config demo.stripe_account).
     *
     * @return list<array{slug: string, name: string, state: string, city: string, postcode: string, street: string, phone: string, hours: string, busy: float, slot: int, online: bool, facilities: list<string>, courts: list<array{0: string, 1: string, 2: bool, 3: int}>}>
     */
    public static function all(): array
    {
        $badminton = fn (int $count, int $price, string $prefix = 'Court') => array_map(
            fn (int $n) => ["{$prefix} {$n}", 'badminton', true, $price],
            range(1, $count),
        );

        return [
            self::venue('demo-shuttle-house-cheras', 'Shuttle House Cheras', 'kuala-lumpur', 'Cheras', '56000', 'standard', 0.55, $badminton(8, 2200), online: true),
            self::venue('demo-kickoff-arena-shah-alam', 'Kickoff Arena Shah Alam', 'selangor', 'Shah Alam', '40150', 'late', 0.5, [
                ['Pitch A', 'futsal', true, 9000], ['Pitch B', 'futsal', true, 9000], ['Pitch C', 'futsal', false, 7000],
            ]),
            self::venue('demo-rally-point-pj', 'Rally Point Petaling Jaya', 'selangor', 'Petaling Jaya', '46050', 'standard', 0.5, [
                ...$badminton(4, 2400), ['Pickleball 1', 'pickleball', true, 3000], ['Pickleball 2', 'pickleball', true, 3000],
            ]),
            self::venue('demo-dink-club-penang', 'Dink Club George Town', 'pulau-pinang', 'George Town', '10450', 'early', 0.45, [
                ['Pickleball 1', 'pickleball', false, 2500], ['Pickleball 2', 'pickleball', false, 2500], ['Pickleball 3', 'pickleball', true, 3200], ['Pickleball 4', 'pickleball', true, 3200],
            ]),
            self::venue('demo-smash-centre-kepong', 'Smash Centre Kepong', 'kuala-lumpur', 'Kepong', '52100', 'friday_break', 0.45, $badminton(6, 1800)),
            self::venue('demo-goal-zone-subang', 'Goal Zone Subang Jaya', 'selangor', 'Subang Jaya', '47500', 'late', 0.45, [
                ['Futsal 1', 'futsal', true, 8500], ['Futsal 2', 'futsal', true, 8500], ...$badminton(3, 2000),
            ]),
            self::venue('demo-net-play-puchong', 'Net Play Puchong', 'selangor', 'Puchong', '47100', 'standard', 0.4, $badminton(5, 1800)),
            self::venue('demo-klang-sports-hub', 'Klang Sports Hub', 'selangor', 'Klang', '41200', 'friday_break', 0.35, [
                ...$badminton(4, 1600), ['Futsal', 'futsal', false, 6500],
            ]),
            self::venue('demo-kajang-court-house', 'Kajang Court House', 'selangor', 'Kajang', '43000', 'standard', 0.35, [
                ...$badminton(4, 1700), ['Pickleball', 'pickleball', true, 2400],
            ]),
            self::venue('demo-seremban-shuttle-hall', 'Seremban Shuttle Hall', 'negeri-sembilan', 'Seremban', '70200', 'friday_break', 0.35, $badminton(6, 1500)),
            self::venue('demo-melaka-futsal-park', 'Melaka Futsal Park', 'melaka', 'Melaka', '75200', 'late', 0.4, [
                ['Pitch 1', 'futsal', false, 6000], ['Pitch 2', 'futsal', false, 6000], ['Pitch 3', 'futsal', true, 7500],
            ]),
            self::venue('demo-ipoh-racket-club', 'Ipoh Racket Club', 'perak', 'Ipoh', '30450', 'early', 0.35, [
                ...$badminton(4, 1500), ['Pickleball 1', 'pickleball', false, 2000], ['Pickleball 2', 'pickleball', false, 2000],
            ]),
            self::venue('demo-bayan-lepas-arena', 'Bayan Lepas Arena', 'pulau-pinang', 'Bayan Lepas', '11900', 'standard', 0.4, [
                ...$badminton(4, 2000), ['Futsal', 'futsal', true, 8000],
            ]),
            self::venue('demo-alor-setar-badminton', 'Alor Setar Badminton Centre', 'kedah', 'Alor Setar', '05100', 'friday_break', 0.3, $badminton(5, 1400)),
            self::venue('demo-kota-bharu-sports', 'Kota Bharu Sports Centre', 'kelantan', 'Kota Bharu', '15150', 'friday_break', 0.3, [
                ...$badminton(4, 1300), ['Futsal', 'futsal', false, 5500],
            ]),
            self::venue('demo-kuantan-beach-courts', 'Kuantan Beach Courts', 'pahang', 'Kuantan', '25200', 'early', 0.3, [
                ['Pickleball 1', 'pickleball', false, 2000], ['Pickleball 2', 'pickleball', false, 2000], ...$badminton(3, 1500),
            ]),
            self::venue('demo-jb-smash-arena', 'JB Smash Arena', 'johor', 'Johor Bahru', '80300', 'late', 0.5, [
                ...$badminton(6, 2000), ['Pickleball', 'pickleball', true, 2800],
            ]),
            self::venue('demo-kuching-court-club', 'Kuching Court Club', 'sarawak', 'Kuching', '93350', 'standard', 0.35, [
                ...$badminton(4, 1600), ['Futsal', 'futsal', true, 7000],
            ]),
            self::venue('demo-kk-futsal-centre', 'Kota Kinabalu Futsal Centre', 'sabah', 'Kota Kinabalu', '88300', 'late', 0.4, [
                ['Pitch 1', 'futsal', true, 7500], ['Pitch 2', 'futsal', true, 7500],
            ]),
            self::venue('demo-putrajaya-racquet-hall', 'Putrajaya Racquet Hall', 'putrajaya', 'Putrajaya', '62000', 'friday_break', 0.3, [
                ...$badminton(4, 1800), ['Pickleball 1', 'pickleball', true, 2600], ['Pickleball 2', 'pickleball', true, 2600],
            ]),
        ];
    }

    /**
     * @param  list<array{0: string, 1: string, 2: bool, 3: int}>  $courts
     * @return array{slug: string, name: string, state: string, city: string, postcode: string, street: string, phone: string, hours: string, busy: float, slot: int, online: bool, facilities: list<string>, courts: list<array{0: string, 1: string, 2: bool, 3: int}>}
     */
    private static function venue(string $slug, string $name, string $state, string $city, string $postcode, string $hours, float $busy, array $courts, bool $online = false): array
    {
        // Small, steady differences between venues, worked out from the address so they never change.
        $n = crc32($slug);

        return [
            'slug' => $slug,
            'name' => "{$name} (Demo)",
            'state' => $state,
            'city' => $city,
            'postcode' => $postcode,
            'street' => 'Lot '.(1 + $n % 40).', Jalan Contoh '.(1 + $n % 9),
            // +60 3-0000 xxxx: a made-up number in Malaysia's format.
            'phone' => '+6030000'.str_pad((string) ($n % 10000), 4, '0', STR_PAD_LEFT),
            'hours' => $hours,
            'busy' => $busy,
            'slot' => $n % 3 === 0 ? 30 : 60,
            'online' => $online,
            'facilities' => array_values(array_filter(
                ['parking', 'toilets', 'surau', 'showers', 'changing_rooms', 'lockers', 'canteen', 'wifi', 'air_conditioning', 'equipment_rental'],
                fn (string $facility, int $i) => $i < 3 || ($n >> $i) % 2 === 1,
                ARRAY_FILTER_USE_BOTH,
            )),
            'courts' => $courts,
        ];
    }

    /**
     * Fictional players: [name, part of the address before @demo.example.com].
     *
     * @return list<array{0: string, 1: string}>
     */
    public static function players(): array
    {
        $names = [
            'Aina Rahman', 'Muhammad Hafiz', 'Tan Wei Ling', 'Siti Nurhaliza Omar', 'Rajesh Kumar', 'Lim Jun Hao',
            'Nurul Izzah Ahmad', 'Arjun Nair', 'Chong Mei Yee', 'Ahmad Faiz', 'Priya Devi', 'Ng Kok Wai',
            'Farah Aziz', 'Daniel Lee', 'Haziq Iskandar', 'Kavitha Raman', 'Wong Jia Hui', 'Amirul Hakim',
            'Melissa Tan', 'Syafiq Rosli', 'Deepa Menon', 'Ong Zhi Wei', 'Nadia Kamal', 'Hairul Nizam',
            'Chen Xin Yi', 'Suresh Pillai', 'Liyana Hassan', 'Goh Boon Keat', 'Izzati Yusof', 'Vinod Krishnan',
            'Teoh Shu Min', 'Aiman Zulkifli', 'Shalini Ganesan', 'Yap Kah Seng', 'Balqis Mahmud', 'Kumar Selvam',
            'Lee Hui Wen', 'Zulkarnain Ali', 'Anis Sofea', 'Koh Wen Jie',
        ];

        return array_map(fn (string $name) => [$name, strtolower(str_replace(' ', '.', $name))], $names);
    }

    /**
     * Names staff type in for walk-ins and phone bookings.
     *
     * @return list<string>
     */
    public static function walkIns(): array
    {
        return ['Encik Rosli', 'Kak Ros', 'Mr Tan', 'Ah Seng', 'Puan Salmah', 'Ravi', 'Uncle Lim', 'Badrul', 'Jessica', 'Hakim'];
    }
}
