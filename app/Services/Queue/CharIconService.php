<?php
namespace App\Services\Queue;

use App\Models\Character\Character;
use App\Models\User\User;
use App\Services\Service;
use Auth;
use DB;
use Intervention\Image\Facades\Image;

class CharIconService extends Service
{

    /**
     * Retrieves any data that should be used in the queue type editing form on the admin side
     *
     * @return array
     */
    public function getEditData()
    {

        return [

        ];
    }

    /**
     * Retrieves any data that should be used in the queue type on the user side
     *
     * @return array
     */
    public function getActData($queue)
    {
        return [
            'charSelect'  => Auth::user()->characters->pluck('fullName', 'slug'),
            'userOptions' => ['' => 'Select User'] + User::visible()->orderBy('name')->get()->pluck('verified_name', 'id')->toArray(),
        ];
    }

    /**
     * Processes the data attribute of the queue and returns it in the preferred format.
     *
     * @param  string  $tag
     * @return mixed
     */
    public function getData($data)
    {
        return $data;
    }

    /**
     * Processes the data attribute of the queue and returns it in the preferred format.
     *
     * @param  object  $queue
     * @param  array   $data
     * @return bool
     */
    public function updateData($queue, $data)
    {

        return [
        ];
    }

    /**
     * Handle any validation on-submit to the queue.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function submit($queue, $data, $user, $submission)
    {
        DB::beginTransaction();

        try {

            //any data handled here should only be that which is required by this particular queue type, as the rest is already handled by the queue service itself

            //let's start by validating the input we have from the user :tm:

            if (! isset($data['slug'])) {
                throw new \Exception("You must select at least one character.");
            }

            foreach ($data['slug'] as $slug) {
                $queue->generalService->checkCharacterOwnership($slug, $user);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Delete the associated data that is custom to this queue.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function delete($queue, $data, $user, $submission)
    {
        DB::beginTransaction();

        try {
            //handle any custom delete functions

            if ($submission->characters->count()) {
                foreach ($submission->characters as $character) {
                    if ($queue->customImageExists($submission->id . '-' . $character->character_id)) {
                        $this->deleteImage($queue->customImagePath, $queue->CustomImageFileName($submission->id . '-' . $character->character_id));
                    } else {
                        throw new \Exception("A custom icon does not exist. Something has gone terribly wrong.");
                    }
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Process character attachments per-character.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function processCharacterAttachments($queue, $data, $submission)
    {
        DB::beginTransaction();

        try {
            if (isset($data['icon'][$data['character_id']])) {
                foreach ($data['icon'][$data['character_id']] as $key => $image) {
                    $imagee = $this->handleImage($data['icon'][$data['character_id']][$key], $queue->customImagePath, $queue->CustomImageFileName($submission->id . '-' . $data['character_id']));
                    unset($data['icon'][$data['character_id']][$key]);
                    if (! $imagee) {
                        throw new \Exception('Error occurred while processing icon image.');
                    }
                }
            } elseif (! $queue->customImageExists($submission->id . '-' . $data['character_id'])) {
                throw new \Exception('No image given for at least one character.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Process and get any other data to be saved in the submission itself.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function finalizeCharacterAttachments($queue, $data, $submission)
    {

        if (isset($data['artist_id'][$data['character_id']])) {
            foreach ($data['artist_id'][$data['character_id']] as $key => $image) {
                $id = $data['artist_id'][$data['character_id']][$key];

            }
        } else {
            $id = null;
        }

        if (isset($data['artist_url'][$data['character_id']])) {
            foreach ($data['artist_url'][$data['character_id']] as $key => $image) {
                $url = $data['artist_url'][$data['character_id']][$key];

            }
        } else {
            $url = null;
        }

        return [
            'artist_id'  => $id,
            'artist_url' => $url,
        ];

    }

    /**
     * Delete the associated data that is custom to this queue.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function approve($queue, $data, $user, $submission)
    {
        DB::beginTransaction();

        try {
            //set all the custom icons

            if ($submission->characters->count()) {
                foreach ($submission->characters as $character) {
                    if ($queue->customImageExists($submission->id . '-' . $character->character_id)) {
                        //differentiate from the submission character
                        $chara = $character->character;
                        //copy the image as the icon
                        //we're not gonna delete the original for archival sake
                        $image = Image::make($queue->customImagePath . '/' . $queue->CustomImageFileName($submission->id . '-' . $character->character_id));

                        if (! $image) {
                            throw new \Exception("Failed to copy custom icon.");
                        }
                        $image->save($chara->imagePath . '/' . $chara->imageFileName, 100, 'png');
                        if (! $image) {
                            throw new \Exception("Failed to handle custom icon.");
                        }

                        $cData['has_icon']   = 1;
                        $cData['artist_id']  = isset($character->data['artist_id']) ? $character->data['artist_id'] : null;
                        $cData['artist_url'] = isset($character->data['artist_url']) ? $character->data['artist_url'] : null;

                        $chara->update($cData);

                        unset($cData['has_icon']);
                        unset($cData['artist_id']);
                        unset($cData['artist_url']);
                        unset($image);

                    } else {
                        throw new \Exception("A custom icon does not exist. Something has gone terribly wrong.");
                    }
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

}
