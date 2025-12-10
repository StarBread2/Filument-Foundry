<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// DATABASE
use App\Entity\Color;
use App\Entity\Finish;
use App\Entity\Material;
use App\Entity\User;
use App\Entity\UserOrder;
use App\Entity\OrderState;

// Repositories
use App\Repository\MaterialRepository;
use App\Repository\FinishRepository;
use App\Repository\ColorRepository;
use App\Repository\UserRepository;
use App\Repository\UserOrderRepository;
use App\Repository\LogRepository;

// Adding Users
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;

// Validation
use Symfony\Component\Validator\Validator\ValidatorInterface;

// FILE SHIT
use Symfony\Component\HttpFoundation\File\Exception\FileException;

//SECURITY
use Symfony\Component\Security\Http\Attribute\IsGranted;

//CSRF
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

//LOG SHIT
use App\Service\LogServices;



final class AdminController extends AbstractController
{
    public function __construct(private LogServices $LogService) {}

    #[Route('/management/dashboard', name: 'management_dashboard')]
    public function dashboard(UserOrderRepository $repo): Response
    {
        // All order types counted only once
        $counts = [
            'pending'    => $repo->count(['order_state' => OrderState::PENDING]),
            'processing' => $repo->count(['order_state' => OrderState::PROCESSING]),
            'printing'   => $repo->count(['order_state' => OrderState::PRINTING]),
            'shipped'    => $repo->count(['order_state' => OrderState::SHIPPED]),
            'delivered'  => $repo->count(['order_state' => OrderState::DELIVERED]),
            'cancelled'  => $repo->count(['order_state' => OrderState::CANCELLED]),
        ];

        // Derived values
        $totalOrders = array_sum($counts);  
        $activeProjects = 
            $counts['pending'] +
            $counts['processing'] +
            $counts['printing'] +
            $counts['shipped'];

        // Revenue (from delivered orders)
        $revenue = 0;
        $deliveredOrders = $repo->findBy(['order_state' => OrderState::DELIVERED]);
        foreach ($deliveredOrders as $order) {
            $revenue += (float) $order->getPriceTotal();
        }

        // In Progress = processing + printing
        $counts['in_progress'] = $counts['processing'] + $counts['printing'];

        return $this->render('management/dashboard.html.twig', [
            'counts'         => $counts,
            'totalOrders'    => $totalOrders,
            'activeProjects' => $activeProjects,
            'delivered'      => $counts['delivered'],
            'revenue'        => $revenue,
        ]);
    }



    #****************************************ORDERS****************************************#
        # CREATE
        #[Route('/management/orders/create', name: 'management_orders_create', methods: ['POST'])]
        public function createOrder(
            Request $request,
            EntityManagerInterface $em,
            MaterialRepository $materialRepo,
            FinishRepository $finishRepo,
            ColorRepository $colorRepo,
            UserRepository $userRepo,
            CsrfTokenManagerInterface $csrfTokenManager
        ): JsonResponse {
            try {
                $data = json_decode($request->getContent(), true);

                // CSRF check
                $csrfToken = new CsrfToken('create_order', $data['_csrf_token'] ?? '');
                if (!$csrfTokenManager->isTokenValid($csrfToken)) {
                    throw new \Exception('Invalid CSRF token.');
                }

                // Fetch related entities
                $user = $userRepo->find($data['userId']);
                $material = $materialRepo->find($data['materialId']);
                $finish = $finishRepo->find($data['finishId']);
                $color = $colorRepo->find($data['colorId']);

                if (!$user || !$material || !$finish || !$color) {
                    $this->addFlash('error', "Invalid user, material, finish, or color selected.");
                    throw new \Exception("Invalid user, material, finish, or color selected.");
                }

                // Create new UserOrder
                $order = new UserOrder();

                //RANDOMIZE MODEL MULTIPLIER (0-1) IF NOT PROVIDED
                    // Handle modelMultiplier logic
                    $rawMultiplier = $data['modelMultiplier'] ?? null;
                    // If null, empty, or zero → generate 0–1 random float
                    if ($rawMultiplier === null || $rawMultiplier === '' || (float)$rawMultiplier == 0) {
                        $modelMultiplier = mt_rand(1, 100) / 100;  // 0.01 to 1.00
                    } else {
                        $modelMultiplier = (float)$rawMultiplier;
                    }

                $order->setUser($user)
                    ->setMaterial($material)
                    ->setFinish($finish)
                    ->setColor($color)
                    ->setQuantity((int)($data['quantity'] ?? 1))
                    ->setModelMultiplier($modelMultiplier)
                    ->setFilePath($data['filePath'] ?? null)
                    ->setDeliveryLocation($data['deliveryLocation'] ?? null)
                    ->setNotes($data['notes'] ?? null)
                    ->setOrderState(OrderState::PENDING)
                    ->setCreatedAt(new \DateTime());

                // Optional delivery dates
                if (!empty($data['deliveryDate'])) {
                    $order->setDeliveryDate(new \DateTime($data['deliveryDate']));
                }
                if (!empty($data['arrivalDate'])) {
                    $order->setDeliveryArrival(new \DateTime($data['arrivalDate']));
                }

                $em->persist($order);
                $em->flush();
                $this->addFlash('success', 'Order created successfully!');

                // Log action manually (flush first (no id yet))
                $this->LogService->logAction($order->getId(), 'Create', 'User Order', true); 

                return new JsonResponse(['success' => true, 'orderId' => $order->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
                return new JsonResponse(['success' => false, 'error' => $e->getMessage()]);
            }
        }
        
        # READ (NO CSRF (ONLY GET))
        #[Route('/management/orders', name: 'management_orders', methods: ['GET'])]
        public function orders(
            Request $request,
            UserOrderRepository $repo,
            MaterialRepository $materialRepository,
            FinishRepository $finishRepository,
            UserRepository $userRepository,
            ColorRepository $colorRepository): Response
        {
            // Read selected filters (from dropdown)
            $status = $request->query->get('status', 'all');  // default = all
            $sort   = $request->query->get('sort', 'id');     // default = id

            // Fetch orders
            $orders = $repo->findFilteredAndSorted($status, $sort);

            // Get dashboard counts
            $totalOrders = $repo->count([]);
            $countPrinting = $repo->count(['order_state' => OrderState::PRINTING]);
            $countCancelled = $repo->count(['order_state' => OrderState::CANCELLED]);
            $countCompleted = $repo->count(['order_state' => OrderState::DELIVERED]);

            return $this->render('management/orders.html.twig', [
                'orders' => $orders,
                'status' => $status,
                'sort' => $sort,
                'totalOrders' => $totalOrders,
                'countPrinting' => $countPrinting,
                'countCancelled' => $countCancelled,
                'countCompleted' => $countCompleted,
                'users' => $userRepository->findNonAdminUsers(),
                'materials' => $materialRepository->findBy(['availability' => true]),
                'finishes' => $finishRepository->findBy(['availability' => true]),
                'colors' => $colorRepository->findBy(['availability' => true]),
            ]);
        }

        # UPDATE
        #[Route('/management/orders/update/{id}', name: 'management_orders_update', methods: ['POST'])]
        public function updateOrder(
            Request $request,
            UserOrder $order,
            EntityManagerInterface $em,
            MaterialRepository $materialRepo,
            FinishRepository $finishRepo,
            ColorRepository $colorRepo,
            CsrfTokenManagerInterface $csrfTokenManager
        ): JsonResponse
        {
            try {
                $data = json_decode($request->getContent(), true);

                // CSRF check
                $csrfToken = new CsrfToken('update_order', $data['_csrf_token'] ?? '');
                if (!$csrfTokenManager->isTokenValid($csrfToken)) {
                    throw new \Exception('Invalid CSRF token.');
                }

                $isEditing = $data['isEditing'] ?? false;

                // Only update editable fields if in editing mode
                if ($isEditing) {
                    // Fetch related entities
                    $material = $materialRepo->find($data['materialId']);
                    $finish   = $finishRepo->find($data['finishId']);
                    $color    = $colorRepo->find($data['colorId']);

                    if (!$material || !$finish || !$color) {
                        throw new \Exception("Invalid material, finish, or color selected.");
                    }

                    // Update fields
                    $order->setQuantity((int)$data['quantity']);
                    $order->setModelMultiplier((float)$data['modelMultiplier']);
                    $order->setMaterial($material);
                    $order->setFinish($finish);
                    $order->setColor($color);

                    // Update delivery location only when editing
                    $order->setDeliveryLocation($data['deliveryLocation'] ?? $order->getDeliveryLocation());
                }

                // Delivery date
                if (!empty($data['deliveryDate'])) {
                    $order->setDeliveryDate(new \DateTime($data['deliveryDate']));
                }

                // Arrival date
                if (!empty($data['arrivalDate'])) {
                    $order->setDeliveryArrival(new \DateTime($data['arrivalDate']));
                }

                $this->LogService->logAction($order->getId(), 'Update', 'User Order'); 
                $em->flush();

                // Flash message
                $this->addFlash('success', 'Order updated successfully!');

                return $this->json(['success' => true]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to update order: ' . $e->getMessage());

                return $this->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
        }
        #UPDATE (STATUS LABEL ONLY (FOR INPUT))
        #[Route('/management/orders/update-status/{id}', name: 'management_orders_update_status', methods: ['POST'])]
        public function updateOrderStatus(
            Request $request,
            UserOrder $order,
            EntityManagerInterface $em,
            CsrfTokenManagerInterface $csrfTokenManager
        ): JsonResponse {
            
            $data = json_decode($request->getContent(), true);

            // CSRF check
            $csrfToken = new CsrfToken('update_status', $data['_csrf_token'] ?? '');
            if (!$csrfTokenManager->isTokenValid($csrfToken)) {
                return new JsonResponse(['success' => false, 'error' => 'Invalid CSRF token']);
            }

            $newStatus = $data['newStatus'] ?? null;

            if (!$newStatus) {
                return new JsonResponse(['success' => false, 'error' => 'No status provided']);
            }

            $this->LogService->logAction($order->getId(), 'Update Status', 'User Order'); 

            // Update only the order state
            $order->setOrderState(OrderState::from($newStatus));
            $em->flush();

            $this->addFlash('success', 'Order status updated successfully!');

            return new JsonResponse(['success' => true]);
        }





        # DELETE
        #[Route('/management/orders/delete/{id}', name: 'management_orders_delete', methods: ['POST'])]
        public function deleteOrder(
            Request $request,
            UserOrder $order,
            EntityManagerInterface $em,
            CsrfTokenManagerInterface $csrfTokenManager
        ): JsonResponse {
            try {
                $data = json_decode($request->getContent(), true);

                // CSRF check
                $csrfToken = new CsrfToken('delete_order', $data['_csrf_token'] ?? '');
                if (!$csrfTokenManager->isTokenValid($csrfToken)) {
                    return new JsonResponse(['success' => false, 'error' => 'Invalid CSRF token.']);
                }

                
                // Delete the order
                $em->remove($order);
                $this->LogService->logAction($order->getId(), 'Delete', 'User Order');
                $em->flush();

                $this->addFlash('success', 'Order deleted successfully.');

                return new JsonResponse(['success' => true]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to delete order: ' . $e->getMessage());

                return new JsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
        }
    #****************************************ORDERS****************************************#



    #****************************************USERS****************************************#
    #https://chatgpt.com/c/68d265c4-7ea8-8332-9ef4-ca7b35bdb23a
        # CREATE
        #[Route('/management/add-user', name: 'management_add_user', methods: ['POST'])]
        public function addUser(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, CsrfTokenManagerInterface $csrfTokenManager): Response
        {
            if (!$this->isGranted('ROLE_ADMIN')) 
            {
                return $this->redirectToRoute('management_dashboard');
            }

            // CSRF check
            $submittedToken = $request->request->get('_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('create_user', $submittedToken))) {
                $this->addFlash('error', 'Invalid CSRF token.');
                return $this->redirectToRoute('management_users');
            }

            $user = new User();
            $user->setPassword(password_hash($request->request->get('password'), PASSWORD_BCRYPT));
            $user->setFullName($request->request->get('fullName'));
            $user->setEmail($request->request->get('email'));
            $user->setAddress($request->request->get('address'));
            $user->setCreatedAt(new \DateTime());
            $user->setActive(true);

            // twig returns 'admin' or 'user' then manually setroles depending on that
            if ($request->request->get('role') === 'admin') 
            {
                $user->setRoles(['ROLE_ADMIN']);
            } 
            elseif ($request->request->get('role') === 'worker') 
            {
                $user->setRoles(['ROLE_WORKER']);
            }
            else
            {   
                $user->setRoles(['ROLE_CUSTOMER']);
            }   

            //Validate
            $errors = $validator->validate($user);

            if (count($errors) > 0) 
            {
                // You can return errors or flash messages
                $errorMessages = [];
                foreach ($errors as $error) 
                {
                    $errorMessages[] = $error->getMessage();
                }

                $this->addFlash('error', implode('<br>', $errorMessages));
                return $this->redirectToRoute('management_users');
            }
                
            $em->persist($user);
            $em->flush();

            // Log action manually (flush first (no id yet))
            $this->LogService->logAction($user->getId(), 'Create', 'User', true); 

            $this->addFlash('success', 'User added successfully!');
            return $this->redirectToRoute('management_users');
        }
        
        # READ
        #[Route('/management/users', name: 'management_users')]
        public function users(UserRepository $userRepository): Response
        {
            if (!$this->isGranted('ROLE_ADMIN')) 
            {
                return $this->redirectToRoute('management_dashboard');
            }

            $users = $userRepository->findAll();

            $totalUsers = count($users);
            $totalAdmins = 0;
            $totalCustomers = 0;
            $totalWorkers = 0;

            foreach ($users as $user) 
            {
                if (in_array('ROLE_ADMIN', $user->getRoles())) 
                {
                    $totalAdmins++; // count admin users
                } 
                else if(in_array('ROLE_WORKER', $user->getRoles()))
                {
                    $totalWorkers++; // count workers users
                }
                else
                {
                    $totalCustomers++; // count non worker users
                }
            }


            return $this->render('management/users.html.twig', [
                'users' => $users,
                'totalUsers' => $totalUsers,
                'totalAdmins' => $totalAdmins,
                'totalCustomers' => $totalCustomers,
                'totalWorkers' => $totalWorkers,
            ]);
        }

        # UPDATE
        #[Route('/management/edit-user/{id}', name: 'management_edit_user', methods: ['POST'])]
        #[IsGranted('ROLE_ADMIN')]
        public function editUser(Request $request, User $user, EntityManagerInterface $em): Response
        {
            // CSRF check
            $submittedToken = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('edit_user', $submittedToken)) {
                $this->addFlash('error', 'Invalid CSRF token.');
                return $this->redirectToRoute('management_users');
            }

            $user->setEmail($request->request->get('email'));
            $user->setFullName($request->request->get('fullName'));
            $user->setAddress($request->request->get('address'));
            
            // twig returns 'admin' or 'user' then manually setroles depending on that
            if ($request->request->get('role') === 'admin') 
            {
                $user->setRoles(['ROLE_ADMIN']);
            } 
            elseif ($request->request->get('role') === 'worker') 
            {
                $user->setRoles(['ROLE_WORKER']);
            }
            else
            {   
                $user->setRoles(['ROLE_CUSTOMER']);
            } 

            // Active status
            $active = $request->request->get('active');
            $user->setActive($active == "1" ? true : false);

            $newPassword = $request->request->get('password');
            if (!empty($newPassword)) 
            {
                $user->setPassword(password_hash($newPassword, PASSWORD_BCRYPT));
            }

            $this->LogService->logAction($user->getId(), 'Update', 'User'); 
            $em->flush();


            $this->addFlash('success', 'User updated successfully!');
            return $this->redirectToRoute('management_users');
        }

        # DELETE
        #[Route('/management/delete-user/{id}', name: 'management_delete_user', methods: ['POST'])]
        #[IsGranted('ROLE_ADMIN')]
        public function deleteUser(User $user, EntityManagerInterface $em, Request $request): Response
        {
            // CSRF check
            $submittedToken = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('delete_user', $submittedToken)) {
                $this->addFlash('error', 'Invalid CSRF token.');
                return $this->redirectToRoute('management_users');
            }

            if ($this->isCsrfTokenValid('delete_user', $request->request->get('_token'))) 
            {
                $em->remove($user);
                $this->LogService->logAction($user->getId(), 'Delete', 'User'); 
                $em->flush();
                $this->addFlash('success', 'User deleted successfully!');
            } 
            else 
            {
                $this->addFlash('error', 'Invalid CSRF token.');
            }

            return $this->redirectToRoute('management_users');
        }
    #****************************************USERS****************************************#



    #****************************************MATERIALS****************************************#
        # CREATE
        #[Route('/management/add-material', name: 'management_add_material', methods: ['POST'])]
        public function addMaterial(Request $request, EntityManagerInterface $em): Response
        {
            // CSRF check
            $submittedToken = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('add_material', $submittedToken)) {
                $this->addFlash('error', 'Invalid CSRF token.');
                return $this->redirectToRoute('management_users');
            }

            $material = new Material();

            // --- Set basic fields ---
            $material->setName($request->request->get('material_name'));
            $material->setPrice((float)$request->request->get('price'));
            $material->setDetails($request->request->get('description'));
            $material->setProperties($request->request->get('properties') ?? '');
            $material->setAvailability($request->request->get('available') === 'on');

            // GETTING THE ID OF THE MATERIAL (PUSHING THE DATA TO DATABASE TO GET ID)
            $em->persist($material);
            $em->flush();

            // IF THERE IS AN IMAGEFILE
            $imageFile = $request->files->get('image');
            if ($imageFile) 
            {
                // LIMITER
                $maxSize = 5 * 1024 * 1024; // (SIZE) 5 MB
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp']; //IMGS TYPES
                
                // LOCATION OF THE PROJ, THEN PUBLIC
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/Materials';

                // Validate file size
                if ($imageFile->getSize() > $maxSize) 
                {
                    $this->addFlash('error', 'Image too large (max 5 MB).');
                    return $this->redirectToRoute('management_materials');
                }
                // Validate file type
                if (!in_array($imageFile->getMimeType(), $allowedMimeTypes)) 
                {
                    $this->addFlash('error', 'Invalid image format. Only JPG, PNG, or WEBP allowed.');
                    return $this->redirectToRoute('management_materials');
                }


                // MAKING GIBBERISH
                $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME); //FOR FUTURE USE IF NEEDED ORIG NAME (BUT NAH)
                $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);

                //FORMAT EX: material_12_PLA_sample_1738983517.jpg
                $newFilename = sprintf
                (
                    'material_%d_%s_%s.%s',
                    $material->getId(),
                    $safeName,
                    time(),
                    $imageFile->guessExtension()
                );

                // Make sure upload directory exists
                if (!file_exists($uploadsDir)) 
                {
                    mkdir($uploadsDir, 0777, true);
                }

                // Move the uploaded file
                try 
                {
                    $imageFile->move($uploadsDir, $newFilename);
                    $material->setImagePath('uploads/Materials/' . $newFilename);
                } 
                catch (\Exception $e) 
                {
                    $this->addFlash('error', 'Image upload failed: ' . $e->getMessage());
                    return $this->redirectToRoute('management_materials');
                }
            } 
            else 
            {
                // If no file uploaded, set imagePath to null
                $material->setImagePath(null);
            }
            
            $em->flush();
            $this->LogService->logAction($material->getId(), 'Create', 'Material', true); 

            $this->addFlash('success', 'Material added successfully.');
            return $this->redirectToRoute('management_materials');
        }

        # READ
        #[Route('/management/materials', name: 'management_materials')]
        public function materials(MaterialRepository $materialRepository): Response
        {
            $materials = $materialRepository->findAll();

            return $this->render('management/materials.html.twig',[
                'materials' => $materials,
            ]);

        }

        # UPDATE
        #[Route('/management/edit-material/{id}', name: 'management_edit_material', methods: ['POST'])]
        public function editMaterial(Request $request, Material $material, EntityManagerInterface $em): Response
        {
            // CSRF check
            $submittedToken = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('edit_material', $submittedToken)) {
                $this->addFlash('error', 'Invalid CSRF token.');
                return $this->redirectToRoute('management_users');
            }

            $material->setName($request->request->get('material_name'));
            $material->setPrice((float)$request->request->get('price'));
            $material->setDetails($request->request->get('description'));
            $material->setProperties($request->request->get('properties') ?? '');
            $material->setAvailability($request->request->get('available') === 'on');

            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/Materials';
            $imageFile = $request->files->get('image');
            $removeImage = $request->request->get('remove_image');

            // If "Remove Image" is checked
            if ($removeImage && $material->getImagePath()) {
                $oldFile = $this->getParameter('kernel.project_dir') . '/public/' . $material->getImagePath();
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
                $material->setImagePath(null);
            }

            // If a new image was uploaded
            if ($imageFile) {
                $maxSize = 5 * 1024 * 1024; // 5 MB
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

                if ($imageFile->getSize() > $maxSize) {
                    $this->addFlash('error', 'Image too large (max 5 MB).');
                    return $this->redirectToRoute('management_materials');
                }
                if (!in_array($imageFile->getMimeType(), $allowedMimeTypes)) {
                    $this->addFlash('error', 'Invalid image format. Only JPG, PNG, or WEBP allowed.');
                    return $this->redirectToRoute('management_materials');
                }

                // Delete old image first
                if ($material->getImagePath()) {
                    $oldFile = $this->getParameter('kernel.project_dir') . '/public/' . $material->getImagePath();
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                // Generate safe filename
                $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
                $newFilename = sprintf(
                    'material_%d_%s_%s.%s',
                    $material->getId(),
                    $safeName,
                    time(),
                    $imageFile->guessExtension()
                );

                if (!file_exists($uploadsDir)) {
                    mkdir($uploadsDir, 0777, true);
                }

                try {
                    $imageFile->move($uploadsDir, $newFilename);
                    $material->setImagePath('uploads/Materials/' . $newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Image upload failed: ' . $e->getMessage());
                    return $this->redirectToRoute('management_materials');
                }
            }

            $this->LogService->logAction($material->getId(), 'Update', 'Material'); 
            $em->flush();

            $this->addFlash('success', 'Material updated successfully!');
            return $this->redirectToRoute('management_materials');
        }


        # DELETE
        #[Route('/management/delete-material/{id}', name: 'management_delete_material', methods: ['POST'])]
        public function deleteMaterial(Material $material, EntityManagerInterface $em, Request $request): Response
        {
            if ($this->isCsrfTokenValid('delete_material', $request->request->get('_token'))) 
            {
                // Check if material has an image
                if ($material->getImagePath()) {
                    $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $material->getImagePath();

                    // Delete the image file if it exists
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }

                // Remove material from database
                $em->remove($material);
                $this->LogService->logAction($material->getId(), 'Delete', 'Material'); 
                $em->flush();

                $this->addFlash('success', 'Material and its image deleted successfully!');
            } 
            else 
            {
                $this->addFlash('error', 'Invalid CSRF token.');
            }

            return $this->redirectToRoute('management_materials');
        }
    #****************************************MATERIALS****************************************#



    #****************************************FINISHES****************************************#
        # CREATE
        #[Route('/management/add-finish', name: 'management_add_finish', methods: ['POST'])]
        public function addFinish(Request $request, EntityManagerInterface $em): Response
        {
            // CSRF check
            $submittedToken = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('add_finish', $submittedToken)) {
                $this->addFlash('error', 'Invalid CSRF token.');
                return $this->redirectToRoute('management_users');
            }

            $finish = new Finish();

            $finish->setName($request->request->get('finish_name'));
            $finish->setPrice((float)$request->request->get('price'));
            $finish->setDetails($request->request->get('details'));
            $finish->setProperties($request->request->get('properties') ?? '');
            $finish->setAvailability($request->request->get('available') === 'on');

            $em->persist($finish);
            $em->flush();

            // Handle Image
            $imageFile = $request->files->get('image');
            if ($imageFile) 
            {
                $maxSize = 5 * 1024 * 1024;
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/Finishes';

                if ($imageFile->getSize() > $maxSize) 
                {
                    $this->addFlash('error', 'Image too large (max 5 MB).');
                    return $this->redirectToRoute('management_finishes');
                }

                if (!in_array($imageFile->getMimeType(), $allowedMimeTypes)) 
                {
                    $this->addFlash('error', 'Invalid image format. Only JPG, PNG, or WEBP allowed.');
                    return $this->redirectToRoute('management_finishes');
                }

                $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME));
                $newFilename = sprintf(
                    'finish_%d_%s_%s.%s',
                    $finish->getId(),
                    $safeName,
                    time(),
                    $imageFile->guessExtension()
                );

                if (!file_exists($uploadsDir)) 
                {
                    mkdir($uploadsDir, 0777, true);
                }

                try 
                {
                    $imageFile->move($uploadsDir, $newFilename);
                    $finish->setImagePath('uploads/Finishes/' . $newFilename);
                } catch (\Exception $e) 
                {
                    $this->addFlash('error', 'Image upload failed: ' . $e->getMessage());
                    return $this->redirectToRoute('management_finishes');
                }
            } 
            else 
            {
                $finish->setImagePath(null);
            }

            $em->flush();
            $this->LogService->logAction($finish->getId(), 'Create', 'Finish', true); 
            $this->addFlash('success', 'Finish added successfully.');
            return $this->redirectToRoute('management_finishes');
        }

        # READ
        #[Route('/management/finishes', name: 'management_finishes')]
        public function finishes(FinishRepository $finishRepository): Response
        {
            $finishes = $finishRepository->findAll();

            return $this->render('management/finishes.html.twig', [
                'finishes' => $finishes,
            ]);
        }

        # UPDATE
        #[Route('/management/edit-finish/{id}', name: 'management_edit_finish', methods: ['POST'])]
        public function editFinish(Finish $finish, Request $request, EntityManagerInterface $em): Response 
        {
            // CSRF check
            $submittedToken = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('edit_finish', $submittedToken)) {
                $this->addFlash('error', 'Invalid CSRF token.');
                return $this->redirectToRoute('management_users');
            }

            $finish->setName($request->request->get('finish_name'));
            $finish->setDetails($request->request->get('details'));
            $finish->setPrice((float)$request->request->get('price'));
            $finish->setProperties($request->request->get('properties', ''));
            $finish->setAvailability($request->request->has('available'));

            // Handle image upload or removal
            $removeImage = $request->request->get('remove_image');
            $imageFile = $request->files->get('image');

            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/finishes/';

            if ($removeImage && $finish->getImagePath()) 
            {
                $oldPath = $this->getParameter('kernel.project_dir') . '/public/' . $finish->getImagePath();
                if (file_exists($oldPath)) unlink($oldPath);
                $finish->setImagePath(null);
            }

            if ($imageFile) 
            {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($uploadsDir, $newFilename);
                $finish->setImagePath('uploads/finishes/' . $newFilename);
            }

            $this->LogService->logAction($finish->getId(), 'Update', 'Finish'); 
            $em->flush();
            $this->addFlash('success', 'Finish updated successfully!');
            return $this->redirectToRoute('management_finishes');
        }


        # DELETE
        #[Route('/management/delete-finish/{id}', name: 'management_delete_finish', methods: ['POST'])]
        public function deleteFinish(Finish $finish, EntityManagerInterface $em, Request $request): Response
        {
            if ($this->isCsrfTokenValid('delete_finish', $request->request->get('_token'))) 
            {
                // Check if finish has an image
                if ($finish->getImagePath()) 
                {
                    $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $finish->getImagePath();

                    // Delete the image file if it exists
                    if (file_exists($filePath)) 
                    {
                        unlink($filePath);
                    }
                }

                // Remove finish from database
                $em->remove($finish);
                $this->LogService->logAction($finish->getId(), 'Delete', 'Finish'); 
                $em->flush();

                $this->addFlash('success', 'Finish and its image deleted successfully!');
            } 
            else 
            {
                $this->addFlash('error', 'Invalid CSRF token.');
            }

            return $this->redirectToRoute('management_finishes');
        }
    #****************************************FINISHES****************************************#



    #****************************************COLORS****************************************#
        #[Route('/management/add-colors', name: 'management_add_colors', methods: ['POST'])]
        public function addColors(Request $request, EntityManagerInterface $em): Response
        {
            // CSRF check
            $submittedToken = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('add_color', $submittedToken)) {
                $this->addFlash('error', 'Invalid CSRF token.');
                return $this->redirectToRoute('management_users');
            }

            $color = new Color();

            $color->setName($request->request->get('colors_name'));
            $color->setPrice((float)$request->request->get('price'));
            $color->setAvailability($request->request->get('available') === 'on');

            $em->persist($color);
            $em->flush();

            // (Hidden shit outside form (if hex or image input))
            $appearanceType = $request->request->get('appearance_type');

            // IF HEX COLOR
            if ($appearanceType === 'hex') 
            {
                $color->setColorHex($request->request->get('color_hex'));
                $color->setImagePath(null);
            } 
            elseif ($appearanceType === 'image') // IF IMG COLOR
            {
                $imageFile = $request->files->get('image');
                if ($imageFile) 
                {
                    $maxSize = 5 * 1024 * 1024;
                    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                    $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/Colors';

                    if ($imageFile->getSize() > $maxSize) 
                    {
                        $this->addFlash('error', 'Image too large (max 5 MB).');
                        return $this->redirectToRoute('management_colors');
                    }

                    if (!in_array($imageFile->getMimeType(), $allowedMimeTypes)) 
                    {
                        $this->addFlash('error', 'Invalid image format. Only JPG, PNG, or WEBP allowed.');
                        return $this->redirectToRoute('management_colors');
                    }

                    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME));
                    $newFilename = sprintf(
                        'color_%d_%s_%s.%s',
                        $color->getId(),
                        $safeName,  
                        time(),
                        $imageFile->guessExtension()
                    );

                    if (!file_exists($uploadsDir)) 
                    {
                        mkdir($uploadsDir, 0777, true);
                    }
                
                    try 
                    {
                        $imageFile->move($uploadsDir, $newFilename);
                        $color->setImagePath('uploads/Colors/' . $newFilename);
                    } 
                    catch (\Exception $e) 
                    {
                        $this->addFlash('error', 'Image upload failed: ' . $e->getMessage());
                        return $this->redirectToRoute('management_colors');
                    }
                }
                else 
                {
                    $color->setImagePath(null);
                }

                $color->setColorHex(null);
            }

            $em->persist($color);
            $em->flush();

            $this->LogService->logAction($color->getId(), 'Create', 'Color', true); 

            $this->addFlash('success', 'Color added successfully.');
            return $this->redirectToRoute('management_colors');
        }

        # READ
        #[Route('/management/colors', name: 'management_colors')]
        public function colors(colorRepository $colorRepository): Response
        {
            $colors = $colorRepository->findAll();

            return $this->render('management/colors.html.twig',[
                'colors' => $colors,
            ]);
        }

        # UPDATE
        #[Route('/management/edit-color/{id}', name: 'management_edit_color', methods: ['POST'])]
        public function editColor(Color $color, Request $request, EntityManagerInterface $em): Response 
        {
            // CSRF check
            $submittedToken = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('edit_color', $submittedToken)) {
                $this->addFlash('error', 'Invalid CSRF token.');
                return $this->redirectToRoute('management_users');
            }

            $appearanceType = $request->request->get('appearance_type'); // "hex" or "image"
            $color->setName($request->request->get('name'));
            $color->setPrice((float)$request->request->get('price'));
            $color->setAvailability($request->request->has('available'));

            $imageFile = $request->files->get('image');
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/colors/';

            if ($appearanceType === 'hex') {
                // 🎨 If user switched to hex mode
                $color->setColorHex($request->request->get('colorHex'));
                
                // Remove any existing image file
                if ($color->getImagePath()) {
                    $oldPath = $this->getParameter('kernel.project_dir') . '/public/' . $color->getImagePath();
                    if (file_exists($oldPath)) unlink($oldPath);
                    $color->setImagePath(null);
                }
            } 
            elseif ($appearanceType === 'image') {
                // 🖼 User switched to image mode
                $color->setColorHex(null);

                if ($imageFile) {
                    $maxSize = 5 * 1024 * 1024;
                    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

                    if ($imageFile->getSize() > $maxSize) {
                        $this->addFlash('error', 'Image too large (max 5 MB).');
                        return $this->redirectToRoute('management_colors');
                    }

                    if (!in_array($imageFile->getMimeType(), $allowedMimeTypes)) {
                        $this->addFlash('error', 'Invalid image format. Only JPG, PNG, or WEBP allowed.');
                        return $this->redirectToRoute('management_colors');
                    }

                    if (!file_exists($uploadsDir)) mkdir($uploadsDir, 0777, true);

                    // Delete old image first
                    if ($color->getImagePath()) {
                        $oldPath = $this->getParameter('kernel.project_dir') . '/public/' . $color->getImagePath();
                        if (file_exists($oldPath)) unlink($oldPath);
                    }

                    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME));
                    $newFilename = sprintf('color_%d_%s_%s.%s', $color->getId(), $safeName, time(), $imageFile->guessExtension());

                    $imageFile->move($uploadsDir, $newFilename);
                    $color->setImagePath('uploads/colors/' . $newFilename);
                }
            }

            $this->LogService->logAction($color->getId(), 'Update', 'Color'); 
            $em->flush();
            $this->addFlash('success', 'Color updated successfully!');
            return $this->redirectToRoute('management_colors');
        }

        # DELETE
        #[Route('/management/delete-color/{id}', name: 'management_delete_color', methods: ['POST'])]
        public function deleteColor(Color $color, EntityManagerInterface $em, Request $request): Response
        {
            if ($this->isCsrfTokenValid('delete_color', $request->request->get('_token'))) {
                // If color has an image
                if ($color->getImagePath()) {
                    $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $color->getImagePath();
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }

                $em->remove($color);
                $this->LogService->logAction($color->getId(), 'Delete', 'Color'); 
                $em->flush();

                $this->addFlash('success', 'Color deleted successfully!');
            } else {
                $this->addFlash('error', 'Invalid CSRF token.');
            }

            return $this->redirectToRoute('management_colors');
        }
    #****************************************COLORS****************************************#



    #[Route('/management/settings', name: 'management_settings')]
    public function settings(): Response
    {
        return $this->render('management/settings.html.twig');
    }

    #[Route('/management/log', name: 'management_log')]
    public function log(LogRepository $logRepository): Response
    {
        // Fetch all logs, newest first
        $logs = $logRepository->findBy([], ['datetimestamp' => 'DESC']);

        return $this->render('management/log.html.twig', [
            'logs' => $logs
        ]);
    }

}
