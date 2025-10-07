<?php

namespace App\Providers;

use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Aws\S3\Exception\S3Exception;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
//    public function boot(): void
//    {
//        // Avoid running during composer/migrations
//        if (app()->runningInConsole()) {
//            return;
//        }
//
//        // Ensure we're only checking for S3 when that disk is actually configured
//        if (config('filesystems.default') !== 's3') {
//            return;
//        }
//
//        $bucket = config('filesystems.disks.s3.bucket');
//
//        try {
//            /** @var S3Client $client */
//            $client = app()->make(S3Client::class);
//
//            // Check bucket existence
//            $buckets = collect($client->listBuckets()['Buckets'])->pluck('Name');
//
//            if (!$buckets->contains($bucket)) {
//                $client->createBucket(['Bucket' => $bucket]);
//                logger()->info("Bucket {$bucket} created successfully.");
//            }
//        } catch (AwsException $e) {
//            logger()->error('Unable to verify/create S3 bucket', [
//                'error' => $e->getMessage(),
//            ]);
//        }
//    }
}
