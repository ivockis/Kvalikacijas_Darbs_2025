<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Category;
use App\Models\Tool;
use App\Models\Image;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Projects with manually defined thematic data...');

        $users = User::all()->keyBy('username');
        $categories = Category::all()->keyBy('name');
        $tools = Tool::all()->keyBy('name');
        $images = Image::all();

        $projectsData = [
            // Dans (Admin) - 3 projects
            [
                'user' => 'dans',
                'title' => 'Handcrafted Oak Coffee Table',
                'description' => 'A beautifully handcrafted coffee table made from solid oak... A perfect centerpiece for any living room.',
                'materials' => 'Solid Oak, Wood Glue, Danish Oil', 'creation_time' => '15 hours', 'is_public' => true, 'is_blocked' => false,
                'categories' => ['Woodworking', 'Home Decor'],
                'tools' => ['Saw', 'Hammer', 'Chisel Set', 'Sandpaper'],
                'cover_image' => 'project_woodworking_1_cover.jpg', 'gallery_images' => ['project_woodworking_1_gallery_1.jpg'],
            ],
            [
                'user' => 'dans',
                'title' => 'Carved Wooden Bird Sculpture',
                'description' => 'A detailed sculpture of a songbird... This piece is intended for indoor display and brings a touch of nature inside.',
                'materials' => 'Basswood Block, Beeswax Polish', 'creation_time' => '8 hours', 'is_public' => true, 'is_blocked' => true,
                'categories' => ['Woodworking', 'Sculpting'],
                'tools' => ['Chisel Set', 'Craft Knife'],
                'cover_image' => 'project_woodworking_2_cover.jpg', 'gallery_images' => [],
            ],
            [
                'user' => 'dans',
                'title' => 'DIY Bookshelf from Reclaimed Wood',
                'description' => 'This project walks through the steps of building a rustic bookshelf from reclaimed pallet wood. It is a great way to recycle and add a unique piece of furniture to your home. The design is simple, requiring minimal complex cuts.',
                'materials' => 'Reclaimed Pallet Wood, Screws, Wood Stain', 'creation_time' => '6 hours', 'is_public' => false, 'is_blocked' => false,
                'categories' => ['Woodworking', 'Home Decor'],
                'tools' => ['Saw', 'Drill', 'Screwdriver Set', 'Sandpaper'],
                'cover_image' => 'project_woodworking_3_cover.jpg', 'gallery_images' => ['project_woodworking_3_gallery_1.jpg', 'project_woodworking_3_gallery_2.jpg'],
            ],

            // Juta - 2 projects
            [
                'user' => 'juta',
                'title' => 'Colorful Crochet Blanket',
                'description' => 'A large, vibrant blanket made with a classic granny square pattern. Uses various colors of soft wool yarn, making it both warm and decorative. This project is great for beginners and can be customized with any color palette.',
                'materials' => 'Multi-colored wool yarn, 5mm crochet hook', 'creation_time' => '25 hours', 'is_public' => true, 'is_blocked' => false,
                'categories' => ['Crochet', 'Quilting', 'Home Decor'],
                'tools' => ['Yarn', 'Needles'],
                'cover_image' => 'project_crochet_1_cover.jpg', 'gallery_images' => ['project_crochet_1_gallery_1.jpg'],
            ],
            [
                'user' => 'juta',
                'title' => 'Origami Crane Mobile',
                'description' => 'A delicate mobile for a nursery or a peaceful corner of your room, made from 100 colorful origami cranes. Legend says a thousand paper cranes grant a wish. This is a start!',
                'materials' => 'Origami paper, String, Wooden hoop', 'creation_time' => '5 hours', 'is_public' => true, 'is_blocked' => false,
                'categories' => ['Origami', 'Paper Craft', 'For Kids'],
                'tools' => ['Craft Knife'],
                'cover_image' => 'project_origami_1_cover.jpg', 'gallery_images' => ['project_origami_1_gallery_1.jpg'],
            ],

            // Enriko - 0 projects


            // Jana (now has Enriko's old projects + her own) - 4 projects
            [
                'user' => 'jana',
                'title' => 'Handmade Ceramic Vase Set',
                'description' => 'A set of three small, elegant vases thrown on a pottery wheel. Each piece is unique, with a speckled blue glaze that gives it a rustic yet modern feel. Perfect for single stem flowers.',
                'materials' => 'Stoneware clay, Blue speckle glaze', 'creation_time' => '6 hours', 'is_public' => true, 'is_blocked' => false,
                'categories' => ['Pottery', 'Home Decor', 'Gardening'],
                'tools' => ['Pottery Wheel', 'Clay'],
                'cover_image' => 'project_pottery_1_cover.jpg', 'gallery_images' => ['project_pottery_1_gallery_1.jpg', 'project_pottery_1_gallery_2.jpg'],
            ],
            [
                'user' => 'jana',
                'title' => 'Welded Metal Rose Sculpture',
                'description' => 'An everlasting rose sculpture created by welding together pieces of scrap metal. A beautiful and permanent piece of art that symbolizes strength and beauty.',
                'materials' => 'Scrap steel, Welding rods', 'creation_time' => '4 hours', 'is_public' => true, 'is_blocked' => false,
                'categories' => ['Metalwork', 'Sculpting'],
                'tools' => ['Soldering Iron', 'Pliers', 'Wrench'],
                'cover_image' => 'project_metalworking_1_cover.jpg', 'gallery_images' => ['project_metalworking_1_gallery_1.jpg'],
            ],
            [
                'user' => 'jana',
                'title' => 'Children\'s Finger Painting Fun',
                'description' => 'An easy and fun art project for kids using non-toxic finger paints. This project encourages creativity and sensory play, allowing children to explore colors and textures. It is perfect for a rainy afternoon activity and results in unique, abstract artworks. The description details steps on preparing a safe workspace, choosing colors, and simple techniques for little artists.',
                'materials' => 'Large paper, Non-toxic finger paints, Apron', 'creation_time' => '1 hour', 'is_public' => true, 'is_blocked' => false,
                'categories' => ['For Kids', 'Painting'],
                'tools' => [],
                'cover_image' => 'project_ForKids_2_cover.jpg', 'gallery_images' => ['project_ForKids_1_cover.jpg', 'project_ForKids_3_cover.jpg'],
            ],
            [
                'user' => 'jana',
                'title' => 'Abstract Canvas Painting',
                'description' => 'Explore the world of abstract art with this vibrant canvas painting project. Using acrylics and various techniques like palette knife and layering, you can create a unique piece that expresses your emotions. This is a complex description of an abstract painting project, detailing the creative process from initial concept to final brushstrokes. It covers color theory, composition, and the emotional impact of different forms and textures.',
                'materials' => 'Large canvas, Acrylic paints, Palette knives', 'creation_time' => '6 hours', 'is_public' => true, 'is_blocked' => false,
                'categories' => ['Painting'],
                'tools' => ['Paint Brushes', 'Easel'],
                'cover_image' => 'project_painting_2_cover.jpg', 'gallery_images' => [],
            ],

            // Marta - 5 projects (2 existing + 3 new)
            [
                'user' => 'marta',
                'title' => 'DIY Garden Planter Box',
                'description' => 'Build a durable and attractive planter box for your backyard or patio. This project uses weather-resistant wood and simple construction techniques, ideal for growing flowers, vegetables, or herbs. The description explains how to select wood, cut pieces, assemble with screws, and apply a protective finish to withstand the elements.',
                'materials' => 'Weather-resistant lumber, Screws, Wood sealant', 'creation_time' => '4 hours', 'is_public' => true, 'is_blocked' => false,
                'categories' => ['Gardening', 'Woodworking', 'Home Decor'],
                'tools' => ['Saw', 'Drill', 'Measuring Tape'],
                'cover_image' => 'project_gardening_2_cover.jpg', 'gallery_images' => [],
            ],
            [
                'user' => 'marta',
                'title' => 'Homemade Soy Candles',
                'description' => 'Learn how to make beautiful and fragrant soy candles at home. This project covers selecting wicks, melting wax, adding essential oils, and pouring candles into various containers. A detailed guide to creating custom candles for gifts or personal use, emphasizing safety precautions and tips for perfect results.',
                'materials' => 'Soy wax flakes, Wicks, Essential oils, Jars', 'creation_time' => '2 hours', 'is_public' => true, 'is_blocked' => false,
                'categories' => ['Candle Making', 'Home Decor'],
                'tools' => [],
                'cover_image' => 'project_pottery_2_cover.jpg', // Reusing pottery image as placeholder for general craft
                'gallery_images' => ['project_pottery_3_cover.jpg'], // Another pottery image
            ],
            [
                'user' => 'marta',
                'title' => 'Rustic Garden Sign',
                'description' => 'Craft a charming rustic sign for your garden, featuring hand-painted lettering and a weather-worn finish. This project combines woodworking and painting skills to create a personalized touch for your outdoor space. A unique and simple project for garden enthusiasts.',
                'materials' => 'Reclaimed wood plank, Outdoor paint, Stencils', 'creation_time' => '2.5 hours', 'is_public' => true, 'is_blocked' => false,
                'categories' => ['Gardening', 'Woodworking', 'Painting'],
                'tools' => ['Sandpaper', 'Paint Brushes'],
                'cover_image' => 'project_gardening_1_cover.jpg', // Using unused gardening image
                'gallery_images' => ['project_gardening_1_gallery_1.jpg'],
            ],
            [
                'user' => 'marta',
                'title' => 'Elegant Crocheted Scarf',
                'description' => 'A stylish and warm scarf crocheted with a delicate lace pattern. This project is suitable for intermediate crocheters and results in a beautiful accessory that can be customized with various yarn colors and textures. The pattern ensures a luxurious feel and a sophisticated look.',
                'materials' => 'Fine wool yarn, Small crochet hook', 'creation_time' => '12 hours', 'is_public' => true, 'is_blocked' => false,
                'categories' => ['Crochet', 'Fashion Design'],
                'tools' => ['Yarn', 'Needles'],
                'cover_image' => 'project_crochet_2_cover.jpg', // Using unused crochet image
                'gallery_images' => [],
            ],
            [
                'user' => 'marta',
                'title' => 'Memory Scrapbook Layouts',
                'description' => 'Design creative and personal scrapbook layouts to beautifully display your cherished photos and memorabilia. This project focuses on composition, color schemes, and embellishments to tell a story on each page. It is an excellent way to organize and present your memories in a visually appealing and durable format.',
                'materials' => 'Scrapbook paper, Photo adhesives, Embellishments, Stickers', 'creation_time' => '5 hours', 'is_public' => true, 'is_blocked' => false,
                'categories' => ['Scrapbooking', 'Photography', 'Paper Craft'],
                'tools' => ['Craft Knife'],
                'cover_image' => 'project_scrapbooking_1_cover.jpg', // Using unused scrapbooking image
                'gallery_images' => [],
            ],
        ];

        foreach ($projectsData as $data) {
            $user = $users->get($data['user']);
            if (!$user) continue;

            $project = Project::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'materials' => $data['materials'],
                'creation_time' => $data['creation_time'],
                'is_public' => $data['is_public'],
                'is_blocked' => $data['is_blocked'],
            ]);

            $categoryIds = $categories->whereIn('name', $data['categories'])->pluck('id');
            $project->categories()->sync($categoryIds);

            $toolIds = $tools->whereIn('name', $data['tools'])->pluck('id');
            $project->tools()->sync($toolIds);

            $coverImage = $images->firstWhere(fn($img) => Str::endsWith($img->path, $data['cover_image']));
            if ($coverImage) {
                $coverImage->update(['project_id' => $project->id, 'is_cover' => true]);
            }
            
            foreach ($data['gallery_images'] as $galleryImageName) {
                $galleryImage = $images->firstWhere(fn($img) => Str::endsWith($img->path, $galleryImageName));
                if ($galleryImage && $galleryImage->id !== optional($coverImage)->id) {
                    $galleryImage->update(['project_id' => $project->id]);
                }
            }
        }
    }
}
