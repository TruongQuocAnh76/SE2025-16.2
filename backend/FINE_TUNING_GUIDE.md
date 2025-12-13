# 🎓 Gemini Fine-Tuning Guide for CertChain

## Overview

This guide explains how to fine-tune Google Gemini AI on your CertChain course data to create a specialized AI assistant that understands your courses and can provide course-specific answers.

## Current Status

✅ **Demo Mode**: The system is currently running in **demo mode** with intelligent responses based on your course data.  
⏳ **Fine-Tuning**: Ready to be deployed once you upgrade to a paid Gemini API plan.

## Why Fine-Tuning?

Fine-tuning trains Gemini specifically on your course content, making it:
- **More Accurate**: Answers specific questions about your courses
- **More Contextual**: Understands course structure and content
- **More Consistent**: Returns responses aligned with your teaching style
- **Offline Ready**: Can be deployed privately

## Prerequisites

You need:
1. **Google Cloud Account** with billing enabled
2. **Gemini API Access** (paid tier, not free)
3. **Training Data** (automatically generated from your courses)

## Step 1: Upgrade to Paid Gemini API

### Free Tier Limitations:
- Only 15 requests/minute
- 1 request/second  
- Rate-limited after ~100 requests/day

### To Upgrade:

1. Go to [Google AI Studio](https://aistudio.google.com/)
2. Click your profile → "Billing" or "Manage my account"
3. Add a payment method
4. Set up a paid project with billing enabled

## Step 2: Generate Training Data

The training data is automatically generated from your courses.

### File: `training_data.jsonl`

Located at: `backend/training_data.jsonl`

Contains:
- Course descriptions
- Module information
- Lesson titles
- General Q&A about CertChain
- **23 training examples** (customizable)

### To regenerate with updated course data:

```bash
cd backend
php create_finetuning_dataset.php
```

This will create/update `training_data.jsonl` with the latest course information.

## Step 3: Create Fine-Tuning Job

### Option A: Using Google AI Studio (Easiest)

1. Go to [Google AI Studio](https://aistudio.google.com/)
2. Click "Create" → "Tuned Model"
3. Upload `training_data.jsonl`
4. Select base model: `gemini-2.0-flash`
5. Configure:
   - Batch size: 4
   - Learning rate: 0.001
   - Epochs: 1
6. Start tuning (takes 30 min - 2 hours)
7. Copy the tuned model name (e.g., `tunedModels/abc123xyz`)

### Option B: Using API (Advanced)

```bash
# Set your API key
export GEMINI_API_KEY="AIzaSyCq3S0uy1xi846TE1704i0e7W8-PIB8gCc"

# Run the fine-tuning script
python backend/finetune_gemini.py
```

## Step 4: Update Configuration

Once fine-tuning is complete:

1. Edit `backend/.env`:
```env
# Replace with your tuned model name from Google AI Studio
GEMINI_MODEL=tunedModels/abc123xyz
AI_DEMO_MODE=false
```

2. Clear cache:
```bash
cd backend
php artisan config:clear
php artisan config:cache
```

3. Test:
```bash
curl -X POST http://localhost:8000/api/ai/chat \
  -H "Content-Type: application/json" \
  -d '{"message":"Tell me about Vue.js course"}'
```

## Fine-Tuning Data Format

The training data uses this format (JSONL):

```json
{
  "text_input": "What is CertChain?",
  "output": "CertChain is an online learning platform using blockchain for certificates..."
}
```

Each line is a separate JSON object. The system automatically generates examples from:
- Course titles and descriptions
- Module content
- Lesson information
- General teaching FAQs

## Customizing Training Data

### Add Custom Examples:

1. Open `backend/create_finetuning_dataset.php`
2. Add to the `$trainingExamples` array:

```php
$trainingExamples[] = [
    'text_input' => 'Your question here',
    'output' => 'Your expected answer here'
];
```

3. Regenerate: `php create_finetuning_dataset.php`
4. Re-run fine-tuning with updated data

### Good Training Examples:

✅ **Good:**
```json
{
  "text_input": "What should I learn in the Vue.js course?",
  "output": "The Vue.js course teaches reactive data binding, component creation, state management with Pinia, and advanced patterns."
}
```

❌ **Bad:**
```json
{
  "text_input": "hey",
  "output": "hi"
}
```

## Monitoring Fine-Tuning

### Via Google AI Studio:
1. Go to [Google AI Studio](https://aistudio.google.com/)
2. Click "My Tuned Models"
3. See status (training, success, or failed)

### Via API:

```bash
curl "https://generativelanguage.googleapis.com/v1beta/tunedModels?key=$GEMINI_API_KEY"
```

## Costs

**Fine-tuning pricing** (as of 2025):

| Action | Cost |
|--------|------|
| Training | $0.05 per 1M tokens |
| Input (tuned) | $0.075 per 1M tokens |
| Output (tuned) | $0.3 per 1M tokens |

**Example**: Fine-tuning 23 training examples (~5K tokens) ≈ **$0.25**

## Troubleshooting

### Issue: "Invalid API key"
- Check if you're using a paid project key
- Free tier keys have rate limiting

### Issue: "Model not found"
- Make sure tuned model name starts with `tunedModels/`
- Copy exact name from Google AI Studio

### Issue: "Training failed"
- Check training data format (must be valid JSONL)
- Ensure no empty lines between JSON objects
- Run: `python -m json.tool training_data.jsonl` to validate

### Issue: "Still getting demo responses"
- Check `AI_DEMO_MODE` in `.env` is set to `false`
- Run `php artisan config:cache`
- Check model name is correct

## After Fine-Tuning

Once you have a fine-tuned model deployed:

1. **Test it**: Ask questions about your courses
2. **Iterate**: Collect user questions, improve training data
3. **Redeploy**: Fine-tune again with better examples
4. **Monitor**: Track quality of responses

## Advanced: Automated Retraining

To automatically retrain when courses are updated:

1. Create a webhook/cron job
2. Run: `php create_finetuning_dataset.php`
3. Check for changes: `git diff training_data.jsonl`
4. If changed, trigger new fine-tuning via API
5. Swap model when complete

## Security

⚠️ **Important:**
- Never commit `.env` with real API keys to GitHub
- Use environment variables in production
- Fine-tuned models are private to your project
- API keys should have minimal required permissions

## Support

For help:
1. Check [Google Gemini Documentation](https://ai.google.dev/)
2. Review [Fine-tuning API Guide](https://ai.google.dev/tutorials/tuning)
3. Check logs: `backend/storage/logs/laravel.log`

---

**Last Updated**: December 14, 2025  
**Status**: Ready for Production Fine-Tuning
