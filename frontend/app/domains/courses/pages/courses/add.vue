<template>
  <div class="min-h-screen bg-background">
    <div class="max-w-7xl mx-auto px-4 py-12">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-text-dark mb-2">Add Course</h1>
        
        <!-- Step Progress Indicator -->
        <div class="mt-6">
          <nav aria-label="Progress">
            <ol role="list" class="space-y-4 md:flex md:space-y-0 md:space-x-8">
              <li class="md:flex-1">
                <div class="group flex flex-col border-l-4 border-teal-600 py-2 pl-4 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4">
                  <span class="text-sm font-medium text-teal-600">Step 1</span>
                  <span class="text-sm font-medium">Course Details</span>
                </div>
              </li>
              <li class="md:flex-1">
                <div :class="[
                  'group flex flex-col border-l-4 py-2 pl-4 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4',
                  currentStep === 2 ? 'border-teal-600' : 'border-gray-200'
                ]">
                  <span :class="[
                    'text-sm font-medium',
                    currentStep === 2 ? 'text-teal-600' : 'text-gray-500'
                  ]">Step 2</span>
                  <span :class="[
                    'text-sm font-medium',
                    currentStep === 2 ? 'text-gray-900' : 'text-gray-500'
                  ]">Modules & Content</span>
                </div>
              </li>
            </ol>
          </nav>
        </div>
      </div>

      <!-- Step 1: Course Details -->
      <div v-if="currentStep === 1">
        <form @submit.prevent="nextStep">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          
          <div class="lg:col-span-2 space-y-6">
            
            <div>
              <label for="title" class="block text-sm font-medium text-text-dark mb-2">
                Name of Course <span class="text-red-500">*</span>
              </label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                placeholder="Enter name course"
                :class="{ 'border-red-500': errors.title }"
              />
              <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
            </div>

            <div>
              <label for="description" class="block text-sm font-medium text-text-dark mb-2">
                Short Description <span class="text-red-500">*</span>
              </label>
              <input
                id="description"
                v-model="form.description"
                type="text"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                placeholder="Enter the short description"
                :class="{ 'border-red-500': errors.description }"
              />
              <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
            </div>
            
            <div>
              <label for="long_description" class="block text-sm font-medium text-text-dark mb-2">
                Description
              </label>
              <textarea
                id="long_description"
                v-model="form.long_description"
                rows="4"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                placeholder="Enter the description"
              ></textarea>
            </div>

            <div>
              <label for="curriculum" class="block text-sm font-medium text-text-dark mb-2">
                Curriculum
              </label>
              <input
                id="curriculum"
                v-model="form.curriculum"
                type="text"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                placeholder="E.g. B = 8 hours"
              />
            </div>

            <div>
              <label for="duration" class="block text-sm font-medium text-text-dark mb-2">
                Duration (In hours)
              </label>
              <input
                id="duration"
                v-model.number="form.duration"
                type="number"
                min="1"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                placeholder="E.g. 8 = 8 hours"
              />
            </div>

            <div class="grid grid-cols-2 gap-6">
              <div>
                <label for="category" class="block text-sm font-medium text-text-dark mb-2">
                  Category
                </label>
                <select
                  id="category"
                  v-model="form.category"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary bg-white"
                >
                  <option value="">Select an Category</option>
                  </select>
              </div>
              <div>
                <label for="language" class="block text-sm font-medium text-text-dark mb-2">
                  Language
                </label>
                <select
                  id="language"
                  v-model="form.language"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary bg-white"
                >
                  <option value="">Select Language</option>
                  </select>
              </div>
            </div> <div>
              <label for="tags" class="block text-sm font-medium text-text-dark mb-2">
                Tags (Hold Ctrl/Cmd to select multiple)
              </label>
              <select
                id="tags"
                v-model="form.tags"
                multiple 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary bg-white"
                size="5" 
              >
                <option v-for="tag in allTags" :key="tag.id" :value="tag.id">
                  {{ tag.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-text-dark mb-2">
                Level <span class="text-red-500">*</span>
              </label>
              <div class="flex space-x-3">
                <button
                  type="button"
                  v-for="level in LEVELS" 
                  :key="level"
                  @click="form.level = level"
                  :class="[
                    'px-6 py-2 rounded-lg font-medium transition-colors',
                    form.level === level
                      ? 'bg-teal-500 text-white shadow-lg'
                      : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                  ]"
                >
                  {{ level.charAt(0) + level.slice(1).toLowerCase() }}
                </button>
              </div>
              <p v-if="errors.level" class="mt-1 text-sm text-red-600">{{ errors.level }}</p>
            </div>

            <div class="grid grid-cols-2 gap-6">
              <div>
                <label for="price" class="block text-sm font-medium text-text-dark mb-2">
                  Price
                </label>
                <input
                  id="price"
                  v-model.number="form.price"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                  placeholder="Enter the Price"
                />
              </div>
              <div>
                <label for="discount" class="block text-sm font-medium text-text-dark mb-2">
                  Discount
                </label>
                <input
                  id="discount"
                  v-model.number="form.discount"
                  type="number"
                  min="0"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                  placeholder="Enter the Discount"
                />
              </div>
            </div>
            
          </div> <div class="lg:col-span-1">
            <label class="block text-sm font-medium text-text-dark mb-2">
              Upload Course Image
            </label>
            
            <div 
              @dragover.prevent="onDragOver"
              @dragleave.prevent="onDragLeave"
              @drop.prevent="onDrop"
              :class="['relative w-full h-80 border-2 border-dashed rounded-lg flex flex-col justify-center items-center text-center p-6 transition-colors',
                isDragging ? 'border-brand-primary bg-blue-50' : 'border-gray-300'
              ]"
            >
              <img v-if="thumbnailPreview" :src="thumbnailPreview" class="absolute inset-0 w-full h-full object-cover rounded-lg" />
              
              <div :class="['relative z-10', { 'bg-white/70 p-4 rounded-lg': thumbnailPreview }]">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p class="mt-2 text-sm text-gray-600">
                  Browse and chose the image you want to upload from your computer
                </p>
                
                <button
                  type="button"
                  @click="triggerFileInput"
                  class="mt-4 px-4 py-2 bg-green-500 text-white rounded-lg font-semibold text-lg"
                >
                  +
                </button>
                
                <input
                  ref="thumbnailFileInput"
                  type="file"
                  accept="image/*"
                  @change="handleThumbnailFileSelect"
                  class="hidden"
                />
              </div>
            </div>
            
            <p v-if="selectedThumbnailFile" class="mt-2 text-sm text-brand-primary font-medium">
              Selected: {{ selectedThumbnailFile.name }}
            </p>
          </div> </div> 
          <div class="mt-12">
            <button
              type="submit"
              class="w-full py-4 bg-teal-500 text-white rounded-lg font-semibold text-lg hover:bg-teal-600 transition-colors"
            >
              Next: Add Modules & Content
            </button>
          </div>
          
          <div v-if="errors.general" class="mt-4 text-center text-red-600">
            {{ errors.general }}
          </div>
        </form>
      </div>

      <!-- Step 2: Modules & Content -->
      <div v-if="currentStep === 2" class="space-y-8">
        <div class="flex justify-between items-center">
          <h2 class="text-2xl font-bold text-text-dark">Modules & Content</h2>
          <button
            type="button"
            @click="addModule"
            class="px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors"
          >
            + Add Module
          </button>
        </div>

        <!-- Modules List -->
        <div v-if="modules.length === 0" class="text-center py-12 text-gray-500">
          <p>No modules added yet. Click "Add Module" to get started.</p>
        </div>

        <div v-else class="space-y-6">
          <div 
            v-for="(module, moduleIndex) in modules" 
            :key="module.id || moduleIndex"
            class="border border-gray-300 rounded-lg p-6"
          >
            <div class="flex justify-between items-start mb-4">
              <h3 class="text-lg font-semibold">Module {{ moduleIndex + 1 }}</h3>
              <button
                type="button"
                @click="removeModule(moduleIndex)"
                class="text-red-500 hover:text-red-700"
              >
                Remove
              </button>
            </div>

            <!-- Module Title and Description -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
              <div>
                <label class="block text-sm font-medium text-text-dark mb-2">
                  Module Title <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="module.title"
                  type="text"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                  placeholder="Enter module title"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-text-dark mb-2">
                  Description (Optional)
                </label>
                <textarea
                  v-model="module.description"
                  rows="2"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                  placeholder="Enter module description"
                ></textarea>
              </div>
            </div>

            <!-- Lessons Section -->
            <div class="mb-6">
              <div class="flex justify-between items-center mb-4">
                <h4 class="text-md font-medium text-text-dark">Lessons</h4>
                <button
                  type="button"
                  @click="addLesson(moduleIndex)"
                  class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm"
                >
                  + Add Lesson
                </button>
              </div>

              <div v-if="module.lessons.length === 0" class="text-gray-500 text-sm">
                No lessons added yet.
              </div>

              <div v-else class="space-y-3">
                <div 
                  v-for="(lesson, lessonIndex) in module.lessons" 
                  :key="lesson.id || lessonIndex"
                  class="bg-gray-50 rounded-lg p-4"
                >
                  <div class="flex justify-between items-start mb-3">
                    <h5 class="font-medium">Lesson {{ lessonIndex + 1 }}</h5>
                    <button
                      type="button"
                      @click="removeLesson(moduleIndex, lessonIndex)"
                      class="text-red-500 hover:text-red-700 text-sm"
                    >
                      Remove
                    </button>
                  </div>

                  <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                    <div>
                      <label class="block text-sm font-medium text-text-dark mb-1">
                        Lesson Title <span class="text-red-500">*</span>
                      </label>
                      <input
                        v-model="lesson.title"
                        type="text"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-brand-primary focus:border-brand-primary text-sm"
                        placeholder="Enter lesson title"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-text-dark mb-1">
                        Video File (MP4) <span class="text-red-500">*</span>
                      </label>
                      <input
                        :ref="`videoFileInput_${moduleIndex}_${lessonIndex}`"
                        type="file"
                        accept="video/mp4"
                        required
                        @change="handleVideoFileSelect($event, moduleIndex, lessonIndex)"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-brand-primary focus:border-brand-primary text-sm"
                      />
                      <p v-if="lesson.video_file" class="mt-1 text-xs text-green-600">
                        Selected: {{ lesson.video_file.name }} ({{ formatFileSize(lesson.video_file.size) }})
                      </p>
                    </div>
                    <div class="flex items-end space-x-2">
                      <div class="flex-1">
                        <label class="block text-sm font-medium text-text-dark mb-1">
                          Duration (min)
                        </label>
                        <input
                          v-model.number="lesson.duration"
                          type="number"
                          min="1"
                          class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-brand-primary focus:border-brand-primary text-sm"
                          placeholder="15"
                        />
                      </div>
                      <div class="flex items-center">
                        <label class="flex items-center text-sm">
                          <input
                            v-model="lesson.is_free"
                            type="checkbox"
                            class="mr-2"
                          />
                          Free
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quizzes Section -->
            <div>
              <div class="flex justify-between items-center mb-4">
                <h4 class="text-md font-medium text-text-dark">Quizzes</h4>
                <button
                  type="button"
                  @click="addQuiz(moduleIndex)"
                  class="px-3 py-1 bg-purple-500 text-white rounded hover:bg-purple-600 text-sm"
                >
                  + Add Quiz
                </button>
              </div>

              <div v-if="module.quizzes.length === 0" class="text-gray-500 text-sm">
                No quizzes added yet.
              </div>

              <div v-else class="space-y-4">
                <div 
                  v-for="(quiz, quizIndex) in module.quizzes" 
                  :key="quiz.id || quizIndex"
                  class="bg-purple-50 rounded-lg p-4"
                >
                  <div class="flex justify-between items-start mb-3">
                    <h5 class="font-medium">Quiz {{ quizIndex + 1 }}</h5>
                    <button
                      type="button"
                      @click="removeQuiz(moduleIndex, quizIndex)"
                      class="text-red-500 hover:text-red-700 text-sm"
                    >
                      Remove
                    </button>
                  </div>

                  <!-- Quiz Basic Info -->
                  <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-4">
                    <div>
                      <label class="block text-sm font-medium text-text-dark mb-1">
                        Quiz Title <span class="text-red-500">*</span>
                      </label>
                      <input
                        v-model="quiz.title"
                        type="text"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-brand-primary focus:border-brand-primary text-sm"
                        placeholder="Enter quiz title"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-text-dark mb-1">
                        Quiz Type
                      </label>
                      <select
                        v-model="quiz.quiz_type"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-brand-primary focus:border-brand-primary text-sm"
                      >
                        <option value="PRACTICE">Practice</option>
                        <option value="GRADED">Graded</option>
                        <option value="FINAL">Final</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-text-dark mb-1">
                        Time Limit (min)
                      </label>
                      <input
                        v-model.number="quiz.time_limit"
                        type="number"
                        min="1"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-brand-primary focus:border-brand-primary text-sm"
                        placeholder="30"
                      />
                    </div>
                  </div>

                  <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-4">
                    <div>
                      <label class="block text-sm font-medium text-text-dark mb-1">
                        Passing Score (%)
                      </label>
                      <input
                        v-model.number="quiz.passing_score"
                        type="number"
                        min="0"
                        max="100"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-brand-primary focus:border-brand-primary text-sm"
                        placeholder="70"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-text-dark mb-1">
                        Max Attempts
                      </label>
                      <input
                        v-model.number="quiz.max_attempts"
                        type="number"
                        min="1"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-brand-primary focus:border-brand-primary text-sm"
                        placeholder="3"
                      />
                    </div>
                  </div>

                  <div class="mb-4">
                    <label class="block text-sm font-medium text-text-dark mb-1">
                      Description (Optional)
                    </label>
                    <textarea
                      v-model="quiz.description"
                      rows="2"
                      class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-brand-primary focus:border-brand-primary text-sm"
                      placeholder="Enter quiz description"
                    ></textarea>
                  </div>

                  <!-- Questions Section -->
                  <div class="mb-4">
                    <div class="flex justify-between items-center mb-3">
                      <h6 class="text-sm font-medium text-text-dark">Questions</h6>
                      <button
                        type="button"
                        @click="addQuestion(moduleIndex, quizIndex)"
                        class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs"
                      >
                        + Add Question
                      </button>
                    </div>

                    <div v-if="quiz.questions.length === 0" class="text-gray-500 text-xs">
                      No questions added yet.
                    </div>

                    <div v-else class="space-y-3">
                      <div 
                        v-for="(question, questionIndex) in quiz.questions" 
                        :key="question.id || questionIndex"
                        class="bg-white rounded border p-3"
                      >
                        <div class="flex justify-between items-start mb-2">
                          <span class="text-sm font-medium">Question {{ questionIndex + 1 }}</span>
                          <button
                            type="button"
                            @click="removeQuestion(moduleIndex, quizIndex, questionIndex)"
                            class="text-red-500 hover:text-red-700 text-xs"
                          >
                            Remove
                          </button>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                          <div>
                            <label class="block text-xs font-medium text-text-dark mb-1">
                              Question Text <span class="text-red-500">*</span>
                            </label>
                            <textarea
                              v-model="question.question_text"
                              rows="2"
                              required
                              class="w-full px-2 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-brand-primary focus:border-brand-primary text-sm"
                              placeholder="Enter your question"
                            ></textarea>
                          </div>

                          <div class="grid grid-cols-1 lg:grid-cols-3 gap-2">
                            <div>
                              <label class="block text-xs font-medium text-text-dark mb-1">
                                Question Type
                              </label>
                              <select
                                v-model="question.question_type"
                                @change="handleQuestionTypeChange(question)"
                                class="w-full px-2 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-brand-primary focus:border-brand-primary text-sm"
                              >
                                <option value="MULTIPLE_CHOICE">Multiple Choice</option>
                                <option value="CHECKBOX">Multiple Select</option>
                                <option value="SHORT_ANSWER">Short Answer</option>
                              </select>
                            </div>
                            <div>
                              <label class="block text-xs font-medium text-text-dark mb-1">
                                Points
                              </label>
                              <input
                                v-model.number="question.points"
                                type="number"
                                min="1"
                                class="w-full px-2 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-brand-primary focus:border-brand-primary text-sm"
                                placeholder="1"
                              />
                            </div>
                          </div>

                          <!-- Options for Multiple Choice/Checkbox -->
                          <div v-if="question.question_type === 'MULTIPLE_CHOICE' || question.question_type === 'CHECKBOX'">
                            <div class="flex justify-between items-center mb-2">
                              <label class="text-xs font-medium text-text-dark">
                                Options <span class="text-red-500">*</span>
                              </label>
                              <button
                                type="button"
                                @click="addOption(question)"
                                class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-xs"
                              >
                                + Add Option
                              </button>
                            </div>
                            <div class="space-y-2">
                              <div 
                                v-for="(option, optionIndex) in question.options" 
                                :key="optionIndex"
                                class="flex items-center space-x-2"
                              >
                                <input
                                  v-model="question.options![optionIndex]"
                                  type="text"
                                  class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm"
                                  placeholder="Enter option text"
                                />
                                <button
                                  type="button"
                                  @click="removeOption(question, optionIndex)"
                                  class="text-red-500 hover:text-red-700 text-xs"
                                >
                                  Remove
                                </button>
                              </div>
                            </div>
                          </div>

                          <!-- Correct Answer -->
                          <div>
                            <label class="block text-xs font-medium text-text-dark mb-1">
                              Correct Answer <span class="text-red-500">*</span>
                            </label>
                            <div v-if="question.question_type === 'MULTIPLE_CHOICE'">
                              <select
                                v-model="question.correct_answer"
                                class="w-full px-2 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-brand-primary focus:border-brand-primary text-sm"
                              >
                                <option value="">Select correct option</option>
                                <option 
                                  v-for="(option, index) in question.options" 
                                  :key="index"
                                  :value="option"
                                >
                                  {{ option }}
                                </option>
                              </select>
                            </div>
                            <div v-else-if="question.question_type === 'CHECKBOX'">
                              <input
                                v-model="question.correct_answer"
                                type="text"
                                class="w-full px-2 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-brand-primary focus:border-brand-primary text-sm"
                                placeholder="Enter correct options separated by commas"
                              />
                            </div>
                            <div v-else>
                              <input
                                v-model="question.correct_answer"
                                type="text"
                                class="w-full px-2 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-brand-primary focus:border-brand-primary text-sm"
                                placeholder="Enter correct answer"
                              />
                            </div>
                          </div>

                          <!-- Explanation -->
                          <div>
                            <label class="block text-xs font-medium text-text-dark mb-1">
                              Explanation (Optional)
                            </label>
                            <textarea
                              v-model="question.explanation"
                              rows="2"
                              class="w-full px-2 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-brand-primary focus:border-brand-primary text-sm"
                              placeholder="Explain why this is the correct answer"
                            ></textarea>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions for Step 2 -->
        <div class="flex justify-between mt-12">
          <button
            type="button"
            @click="previousStep"
            class="py-3 px-6 bg-gray-500 text-white rounded-lg font-semibold hover:bg-gray-600 transition-colors"
          >
            Previous: Course Details
          </button>
          
          <button
            type="button"
            @click="handleSubmit"
            :disabled="isSubmitting"
            class="py-3 px-6 bg-teal-500 text-white rounded-lg font-semibold hover:bg-teal-600 transition-colors disabled:opacity-50"
          >
            <span v-if="isSubmitting">Creating Course...</span>
            <span v-else>Create Course</span>
          </button>
        </div>
        
        <div v-if="errors.general" class="mt-4 text-center text-red-600">
          {{ errors.general }}
        </div>
        <div v-if="successMessage" class="mt-4 text-center text-green-600">
          {{ successMessage }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import type { CreateCourseData, Tag, CreateModuleData, CreateLessonData, CreateQuizData, CreateQuestionData, CreateCourseWithModulesData } from '../../types/course'
const config = useRuntimeConfig()

const LEVELS = ['BEGINNER' , 'INTERMEDIATE' , 'ADVANCED' , 'EXPERT'] as const

const { createCourse, uploadCourseThumbnail, updateCourseThumbnail, getTags, uploadLessonVideo, notifyVideoUploadComplete, checkHlsProcessingStatus } = useCourses()
const router = useRouter()

// Multi-step form state
const currentStep = ref(1)

// Modules state
const modules = ref<CreateModuleData[]>([])

// Module management functions
const addModule = () => {
  const newModule: CreateModuleData = {
    title: '',
    description: '',
    order_index: modules.value.length,
    lessons: [],
    quizzes: []
  }
  modules.value.push(newModule)
}

const removeModule = (moduleIndex: number) => {
  modules.value.splice(moduleIndex, 1)
  // Update order indexes
  modules.value.forEach((module, index) => {
    module.order_index = index
  })
}

// Lesson management functions  
const addLesson = (moduleIndex: number) => {
  const module = modules.value[moduleIndex]
  if (!module) return
  
  const newLesson: CreateLessonData = {
    title: '',
    content_type: 'VIDEO',
    content_url: '',
    duration: undefined,
    order_index: module.lessons.length,
    is_free: false
  }
  module.lessons.push(newLesson)
}

const removeLesson = (moduleIndex: number, lessonIndex: number) => {
  const module = modules.value[moduleIndex]
  if (!module) return
  
  module.lessons.splice(lessonIndex, 1)
  // Update order indexes
  module.lessons.forEach((lesson, index) => {
    lesson.order_index = index
  })
}

// Quiz management functions
const addQuiz = (moduleIndex: number) => {
  const module = modules.value[moduleIndex]
  if (!module) return
  
  const newQuiz: CreateQuizData = {
    title: '',
    description: '',
    quiz_type: 'PRACTICE',
    time_limit: undefined,
    passing_score: 70,
    max_attempts: undefined,
    order_index: module.quizzes.length,
    is_active: true,
    questions: []
  }
  module.quizzes.push(newQuiz)
}

const removeQuiz = (moduleIndex: number, quizIndex: number) => {
  const module = modules.value[moduleIndex]
  if (!module) return
  
  module.quizzes.splice(quizIndex, 1)
  // Update order indexes
  module.quizzes.forEach((quiz, index) => {
    quiz.order_index = index
  })
}

// Question management functions
const addQuestion = (moduleIndex: number, quizIndex: number) => {
  const module = modules.value[moduleIndex]
  if (!module) return
  
  const quiz = module.quizzes[quizIndex]
  if (!quiz) return
  
  const newQuestion: CreateQuestionData = {
    question_text: '',
    question_type: 'MULTIPLE_CHOICE',
    points: 1,
    order_index: quiz.questions.length,
    options: ['', ''],
    correct_answer: '',
    explanation: ''
  }
  quiz.questions.push(newQuestion)
}

const removeQuestion = (moduleIndex: number, quizIndex: number, questionIndex: number) => {
  const module = modules.value[moduleIndex]
  if (!module) return
  
  const quiz = module.quizzes[quizIndex]
  if (!quiz) return
  
  quiz.questions.splice(questionIndex, 1)
  // Update order indexes
  quiz.questions.forEach((question, index) => {
    question.order_index = index
  })
}

const handleQuestionTypeChange = (question: CreateQuestionData) => {
  if (question.question_type === 'MULTIPLE_CHOICE' || question.question_type === 'CHECKBOX') {
    if (!question.options || question.options.length === 0) {
      question.options = ['', '']
    }
  } else {
    question.options = undefined
  }
  question.correct_answer = ''
}

// Option management functions
const addOption = (question: CreateQuestionData) => {
  if (!question.options) {
    question.options = []
  }
  question.options.push('')
}

const removeOption = (question: CreateQuestionData, optionIndex: number) => {
  if (question.options) {
    question.options.splice(optionIndex, 1)
  }
}

// Video file management
const handleVideoFileSelect = (event: Event, moduleIndex: number, lessonIndex: number) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (file) {
    // Validate file type
    if (file.type !== 'video/mp4') {
      errors.value.general = 'Only MP4 video files are allowed.'
      target.value = ''
      return
    }
    
    const lesson = modules.value[moduleIndex]?.lessons[lessonIndex]
    if (lesson) {
      lesson.video_file = file
      // Clear content_url since we're using file upload
      lesson.content_url = undefined
      // Clear any previous errors
      delete errors.value.general
    }
  }
}

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// Step navigation functions
const nextStep = () => {
  if (validateForm()) {
    currentStep.value = 2
  }
}

const previousStep = () => {
  currentStep.value = 1
}

// State cho Dropzone
const isDragging = ref(false)
const thumbnailPreview = ref<string | null>(null)

// Thumbnail handling
const thumbnailType = ref<'url' | 'upload'>('upload')
const selectedThumbnailFile = ref<File | null>(null)
const thumbnailFileInput = ref<HTMLInputElement | null>(null)

// Form data
const form = ref<CreateCourseData>({
  title: '',
  description: '',
  long_description: '', 
  curriculum: '',
  category: '',
  language: '',
  discount: undefined,
  level: '' as any,
  price: undefined,
  duration: undefined,
  passing_score: 70,
  tags: [] 
})

const allTags = ref<Tag[]>([])

// Fetch all tags on mount
onMounted(async () => {
  allTags.value = await getTags()
})

// Form state
const errors = ref<Record<string, string>>({})
const isSubmitting = ref(false)
const successMessage = ref('')

// Thumbnail file selection handler
const handleThumbnailFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    setFile(file)
  }
}

const triggerFileInput = () => {
  thumbnailFileInput.value?.click()
}

const onDragOver = (event: DragEvent) => { isDragging.value = true }
const onDragLeave = (event: DragEvent) => { isDragging.value = false }
const onDrop = (event: DragEvent) => {
  isDragging.value = false
  const file = event.dataTransfer?.files[0]
  if (file && file.type.startsWith('image/')) {
    setFile(file)
  }
}

const setFile = (file: File) => {
  selectedThumbnailFile.value = file
  const reader = new FileReader()
  reader.onload = (e) => {
    thumbnailPreview.value = e.target?.result as string
  }
  reader.readAsDataURL(file)
}

// Form validation
const validateForm = (): boolean => {
  errors.value = {}
  if (!form.value.title.trim()) {
    errors.value.title = 'Course title is required'
  }
  if (!form.value.description.trim()) {
    errors.value.description = 'Course description is required'
  }
  if (!form.value.level) {
    errors.value.level = 'Please select a difficulty level'
  }
  if (form.value.passing_score < 0 || form.value.passing_score > 100) {
    errors.value.passing_score = 'Passing score must be between 0 and 100'
  }
  return Object.keys(errors.value).length === 0
}

// Validation for modules
const validateModules = (): boolean => {
  errors.value = {}
  
  if (modules.value.length === 0) {
    errors.value.general = 'Please add at least one module to the course.'
    return false
  }

  for (let i = 0; i < modules.value.length; i++) {
    const module = modules.value[i]
    if (!module || !module.title.trim()) {
      errors.value.general = `Module ${i + 1} title is required.`
      return false
    }
    
    for (let j = 0; j < module.lessons.length; j++) {
      const lesson = module.lessons[j]
      if (!lesson || !lesson.title.trim()) {
        errors.value.general = `Lesson ${j + 1} in Module ${i + 1} title is required.`
        return false
      }
      if (!lesson.video_file && !lesson.content_url?.trim()) {
        errors.value.general = `Lesson ${j + 1} in Module ${i + 1} video file is required.`
        return false
      }
    }
    
    for (let j = 0; j < module.quizzes.length; j++) {
      const quiz = module.quizzes[j]
      if (!quiz || !quiz.title.trim()) {
        errors.value.general = `Quiz ${j + 1} in Module ${i + 1} title is required.`
        return false
      }
      
      if (quiz.questions.length === 0) {
        errors.value.general = `Quiz ${j + 1} in Module ${i + 1} must have at least one question.`
        return false
      }
      
      for (let k = 0; k < quiz.questions.length; k++) {
        const question = quiz.questions[k]
        if (!question || !question.question_text.trim()) {
          errors.value.general = `Question ${k + 1} in Quiz ${j + 1} (Module ${i + 1}) text is required.`
          return false
        }
        
        if ((question.question_type === 'MULTIPLE_CHOICE' || question.question_type === 'CHECKBOX') && (!question.options || question.options.length < 2)) {
          errors.value.general = `Question ${k + 1} in Quiz ${j + 1} (Module ${i + 1}) must have at least 2 options.`
          return false
        }
        
        if (!question.correct_answer.trim()) {
          errors.value.general = `Question ${k + 1} in Quiz ${j + 1} (Module ${i + 1}) must have a correct answer.`
          return false
        }
      }
    }
  }
  
  return true
}

// Submit handler
const handleSubmit = async () => {
  // Only validate basic form for step 1, both for step 2
  if (currentStep.value === 1) {
    if (!validateForm()) {
      return
    }
  } else {
    if (!validateForm() || !validateModules()) {
      return
    }
  }

  isSubmitting.value = true
  errors.value = {}
  successMessage.value = ''

  try {
    const courseData: CreateCourseWithModulesData = { 
      ...form.value,
      modules: modules.value 
    }
    if (courseData.price === undefined) delete courseData.price
    if (courseData.duration === undefined) delete courseData.duration

    // Only request thumbnail upload if a file is selected
    if (selectedThumbnailFile.value) {
      courseData.thumbnail = 'UPLOAD_REQUESTED'
    }

    const result = await createCourse(courseData)

    if (result) {
      // Upload thumbnail if provided
      if (selectedThumbnailFile.value && result.thumbnail_upload_url) {
        const uploadSuccess = await uploadCourseThumbnail(result.thumbnail_upload_url, selectedThumbnailFile.value)
        // if (uploadSuccess) {
        //   const thumbnailPath = `courses/thumbnails/${result.course.id}.jpg`
        //   const finalThumbnailUrl = `${config.public.awsEndpoint}/${config.public.awsBucket}/${thumbnailPath}`
        // await updateCourseThumbnail(result.course.id, finalThumbnailUrl)
        // } else {
        //   console.warn('Thumbnail upload failed, but course was created successfully')
        // }
      }

      // Upload lesson videos if provided
      if (result.video_upload_urls) {
        successMessage.value = 'Course created! Uploading videos...'
        
        const videoUploads = Object.entries(result.video_upload_urls)
        let uploadedCount = 0
        let failedCount = 0
        
        for (const [key, uploadInfo] of videoUploads) {
          // Parse the key to get module and lesson indices
          const keyParts = key.split('_')
          const moduleIndexStr = keyParts[1]
          const lessonIndexStr = keyParts[3]
          
          if (moduleIndexStr && lessonIndexStr) {
            const moduleIndex = parseInt(moduleIndexStr)
            const lessonIndex = parseInt(lessonIndexStr)
            
            const lesson = modules.value[moduleIndex]?.lessons[lessonIndex]
            if (lesson?.video_file) {
              try {
                successMessage.value = `Uploading video ${uploadedCount + 1} of ${videoUploads.length}...`
                const uploadSuccess = await uploadLessonVideo(
                  uploadInfo.lesson_id, 
                  uploadInfo.upload_url, 
                  lesson.video_file,
                  uploadInfo.video_path,
                  uploadInfo.video_path.replace('.mp4', '')
                )
                
                if (uploadSuccess) {
                  uploadedCount++
                  successMessage.value = `Video ${uploadedCount} of ${videoUploads.length} uploaded and processing started!`
                } else {
                  failedCount++
                  console.warn(`Failed to upload video for lesson ${lesson.title}`)
                }
              } catch (error) {
                failedCount++
                console.warn(`Failed to upload video for lesson ${lesson.title}:`, error)
              }
            }
          }
        }
        
        if (failedCount === 0) {
          successMessage.value = `Course created successfully! All ${uploadedCount} videos uploaded and are being processed. Redirecting...`
        } else {
          successMessage.value = `Course created! ${uploadedCount} videos uploaded, ${failedCount} failed. Check console for details. Redirecting...`
        }
      } else {
        successMessage.value = 'Course created successfully! Redirecting...'
      }

      successMessage.value = successMessage.value + ' Videos will be processed in background and converted to streaming format.'
      
      // Reset form
      form.value = {
        title: '',
        description: '',
        long_description: '', 
        curriculum: '',
        category: '',
        language: '',
        discount: undefined,
        level: '' as any,
        price: undefined,
        duration: undefined,
        passing_score: 70,
        tags: []
      }
      modules.value = []
      currentStep.value = 1
      thumbnailType.value = 'upload'
      selectedThumbnailFile.value = null
      thumbnailPreview.value = null 
      if (thumbnailFileInput.value) {
        thumbnailFileInput.value.value = ''
      }

      // Redirect to courses list after a short delay
      setTimeout(() => {
        router.push('/courses')
      }, 2000)
    }
  } catch (error: any) {
    console.error('Failed to create course:', error)
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errors.value.general = error.message || 'Failed to create course. Please try again.'
    }
  } finally {
    isSubmitting.value = false
  }
}

// Page meta
definePageMeta({
  layout: 'default'
})

// SEO
useHead({
  title: 'Create Course - CertChain',
  meta: [
    { name: 'description', content: 'Create a new course on CertChain platform' }
  ]
})
</script>