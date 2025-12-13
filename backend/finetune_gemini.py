#!/usr/bin/env python3
"""
Google Gemini Fine-tuning Script
This script uploads training data and creates a fine-tuned model
"""

import json
import os
import time
import requests
import sys

# Get API key from environment or command line
api_key = os.environ.get('GEMINI_API_KEY') or (sys.argv[1] if len(sys.argv) > 1 else None)

if not api_key:
    print("Error: GEMINI_API_KEY not found")
    sys.exit(1)

# Read training data
with open('training_data.jsonl', 'r', encoding='utf-8') as f:
    training_data = [json.loads(line) for line in f if line.strip()]

print(f"✅ Loaded {len(training_data)} training examples")

# Upload file to Google's API
print("\n📤 Uploading training data to Gemini...")

# Create file upload request
files = {'file': ('training_data.jsonl', open('training_data.jsonl', 'rb'))}

upload_url = f"https://generativelanguage.googleapis.com/upload/files?key={api_key}"

try:
    # Upload file
    response = requests.post(upload_url, files=files)
    
    if response.status_code == 200:
        file_data = response.json()
        file_uri = file_data['file']['uri']
        print(f"✅ File uploaded successfully!")
        print(f"📁 File URI: {file_uri}")
    else:
        print(f"❌ Upload failed: {response.status_code}")
        print(f"Response: {response.text}")
        sys.exit(1)
        
except Exception as e:
    print(f"❌ Error uploading file: {str(e)}")
    sys.exit(1)

# Create fine-tuning job
print("\n🔧 Creating fine-tuning job...")

finetuning_url = "https://generativelanguage.googleapis.com/v1beta/tuningJobs?key=" + api_key

payload = {
    "display_name": "CertChain Courses Fine-Tuning",
    "training_data": {
        "file_uri": file_uri
    },
    "model": "models/gemini-2.0-flash-001",
    "hyper_parameters": {
        "batch_size": 4,
        "learning_rate": 0.001,
        "epoch_count": 1
    }
}

try:
    response = requests.post(
        finetuning_url,
        json=payload,
        headers={"Content-Type": "application/json"}
    )
    
    if response.status_code == 200:
        job_data = response.json()
        job_id = job_data.get('name', '').split('/')[-1]
        print(f"✅ Fine-tuning job created!")
        print(f"🆔 Job ID: {job_id}")
        print(f"📊 Job Name: {job_data.get('name')}")
        print(f"⏱️  Status: {job_data.get('state')}")
        
        # Save job info
        with open('finetuning_job.json', 'w') as f:
            json.dump(job_data, f, indent=2)
        
        print("\n⏳ Fine-tuning is in progress. This may take 30 minutes to 2 hours.")
        print("📌 Job details saved to: finetuning_job.json")
        print("\n💡 Check status with:")
        print(f"   curl https://generativelanguage.googleapis.com/v1beta/{job_data.get('name')}?key={api_key}")
        
    else:
        print(f"❌ Job creation failed: {response.status_code}")
        print(f"Response: {response.text}")
        sys.exit(1)
        
except Exception as e:
    print(f"❌ Error creating fine-tuning job: {str(e)}")
    sys.exit(1)

print("\n✨ Fine-tuning setup complete!")
