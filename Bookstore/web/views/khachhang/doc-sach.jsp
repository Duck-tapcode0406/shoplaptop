<%-- Trang đọc sách trực tuyến --%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<link rel="stylesheet" href="${baseURL}/css/khachhang/style-account.css">
<style>
    .book-reader {
        max-width: 900px;
        margin: 2rem auto;
        padding: 2rem;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .book-header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #eee;
    }
    .book-header h1 {
        color: #00467f;
        margin-bottom: 0.5rem;
    }
    .book-header .author {
        color: #666;
        font-size: 1.1rem;
    }
    .book-content {
        line-height: 1.8;
        font-size: 1.1rem;
        color: #333;
        text-align: justify;
        white-space: pre-wrap;
        padding: 1.5rem;
        background: #f9f9f9;
        border-radius: 5px;
        min-height: 400px;
    }
    .book-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 2px solid #eee;
    }
    .btn-voice {
        background: #28a745;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
    }
    .btn-voice:hover {
        background: #218838;
    }
    .btn-voice i {
        margin-right: 0.5rem;
    }
    .btn-voice.playing {
        background: #dc3545;
    }
    .btn-voice.playing:hover {
        background: #c82333;
    }
    .voice-controls {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    .voice-speed-control {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-left: 1rem;
    }
    .voice-speed-control label {
        font-size: 0.9rem;
        color: #666;
    }
    .voice-speed-control input {
        width: 80px;
    }
    .voice-select-control {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-left: 1rem;
    }
    .voice-select-control label {
        font-size: 0.9rem;
        color: #666;
    }
    .voice-select-control select {
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.9rem;
        min-width: 200px;
        background: white;
    }
</style>

<title>Đọc Sách - ${sach.tenSanPham}</title>

<jsp:include page="layout/header.jsp" />

<main class="container">
    <div class="book-reader">
        <div class="book-header">
            <h1>${sach.tenSanPham}</h1>
            <p class="author">Tác giả: ${sach.tacGia.hoVaTen}</p>
        </div>
        
        <div class="book-content" id="bookContent">
            <div style="text-align: center; padding: 2rem;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; color: #00467f;"></i>
                <p style="margin-top: 1rem; color: #666;">Đang tải nội dung sách...</p>
            </div>
        </div>
        
        <div class="book-controls">
            <a href="${baseURL}/trang-chu" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Quay lại trang chủ
            </a>
            <div class="voice-controls">
                <button id="voiceBtn" class="btn-voice" onclick="toggleVoiceReading()">
                    <i class="fa-solid fa-volume-high"></i> <span id="voiceBtnText">Đọc bằng giọng AI</span>
                </button>
                <div class="voice-speed-control" id="voiceSpeedControl" style="display: none;">
                    <label>Tốc độ:</label>
                    <input type="range" id="voiceSpeed" min="0.5" max="2" step="0.1" value="1" onchange="updateVoiceSpeed()">
                    <span id="speedValue">1.0x</span>
                </div>
                <div class="voice-select-control" id="voiceSelectControl" style="display: none;">
                    <label>Giọng đọc:</label>
                    <select id="voiceSelect" onchange="updateSelectedVoice()">
                        <option value="">Đang tải giọng đọc...</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
const TTS_API_KEY = '__pltTLY9vd5HQTVZTEDDt6co994ZQKLjU8uzpZXPoyrW';
let speechSynthesis = null;
let currentUtterance = null;
let isPlaying = false;
let currentSpeed = 1.0;
let availableVoices = [];
let sortedVoices = []; // Danh sách giọng đã sắp xếp (tiếng Việt trước)
let selectedVoice = null;

// Khởi tạo Web Speech API
function initSpeechSynthesis() {
    if ('speechSynthesis' in window) {
        speechSynthesis = window.speechSynthesis;
        loadAvailableVoices();
        return true;
    }
    return false;
}

// Tải danh sách giọng đọc có sẵn
function loadAvailableVoices() {
    if (!speechSynthesis) return;
    
    // Lấy danh sách giọng đọc
    const getVoices = () => {
        availableVoices = speechSynthesis.getVoices();
        
        // Lọc các giọng tiếng Việt (ưu tiên)
        const vietnameseVoices = availableVoices.filter(voice => 
            voice.lang.includes('vi') || 
            voice.lang.includes('VN') || 
            voice.name.toLowerCase().includes('vietnamese') ||
            voice.name.toLowerCase().includes('viet nam')
        );
        
        // Lọc các giọng tiếng Anh (fallback)
        const englishVoices = availableVoices.filter(voice => 
            voice.lang.includes('en')
        );
        
        // Sắp xếp: tiếng Việt trước, sau đó tiếng Anh
        sortedVoices = [...vietnameseVoices, ...englishVoices];
        
        // Cập nhật dropdown
        const voiceSelect = document.getElementById('voiceSelect');
        if (voiceSelect) {
            voiceSelect.innerHTML = '';
            
            if (sortedVoices.length > 0) {
                sortedVoices.forEach((voice, index) => {
                    const option = document.createElement('option');
                    option.value = index;
                    option.textContent = `${voice.name} (${voice.lang})`;
                    if (vietnameseVoices.length > 0 && index === 0) {
                        option.selected = true;
                        selectedVoice = voice;
                    }
                    voiceSelect.appendChild(option);
                });
            } else {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Không tìm thấy giọng đọc';
                voiceSelect.appendChild(option);
            }
            
            // Lưu lựa chọn vào localStorage
            const savedVoiceIndex = localStorage.getItem('selectedVoiceIndex');
            if (savedVoiceIndex !== null && sortedVoices[parseInt(savedVoiceIndex)]) {
                voiceSelect.value = savedVoiceIndex;
                selectedVoice = sortedVoices[parseInt(savedVoiceIndex)];
            } else if (sortedVoices.length > 0) {
                // Mặc định chọn giọng tiếng Việt đầu tiên nếu có
                voiceSelect.value = '0';
                selectedVoice = sortedVoices[0];
            }
        }
    };
    
    // Nếu voices đã sẵn sàng
    if (speechSynthesis.getVoices().length > 0) {
        getVoices();
    } else {
        // Đợi voices load xong
        speechSynthesis.onvoiceschanged = getVoices;
    }
}

// Cập nhật giọng đọc được chọn
function updateSelectedVoice() {
    const voiceSelect = document.getElementById('voiceSelect');
    if (voiceSelect && voiceSelect.value !== '') {
        const voiceIndex = parseInt(voiceSelect.value);
        if (sortedVoices && sortedVoices[voiceIndex]) {
            selectedVoice = sortedVoices[voiceIndex];
            localStorage.setItem('selectedVoiceIndex', voiceIndex);
            console.log('Đã chọn giọng đọc: ' + selectedVoice.name + ' (' + selectedVoice.lang + ')');
            
            // Nếu đang đọc, cập nhật giọng cho lần đọc tiếp theo
            if (isPlaying && currentUtterance) {
                currentUtterance.voice = selectedVoice;
                currentUtterance.lang = selectedVoice.lang;
            }
        }
    }
}

// Sử dụng API giọng đọc AI hoặc Web Speech API
async function toggleVoiceReading() {
    const content = document.getElementById('bookContent').innerText;
    const voiceBtn = document.getElementById('voiceBtn');
    const voiceBtnText = document.getElementById('voiceBtnText');
    const speedControl = document.getElementById('voiceSpeedControl');
    
    if (!content || content.trim() === '' || content === 'Nội dung sách đang được cập nhật...') {
        alert('Nội dung sách chưa có. Vui lòng thử lại sau.');
        return;
    }
    
    if (isPlaying) {
        // Dừng đọc
        stopVoiceReading();
        voiceBtn.classList.remove('playing');
        voiceBtnText.innerHTML = '<i class="fa-solid fa-volume-high"></i> Đọc bằng giọng AI';
        speedControl.style.display = 'none';
        const voiceSelectControl = document.getElementById('voiceSelectControl');
        if (voiceSelectControl) {
            voiceSelectControl.style.display = 'none';
        }
        isPlaying = false;
    } else {
        // Bắt đầu đọc
        if (initSpeechSynthesis()) {
            // Sử dụng Web Speech API với giọng tiếng Việt
            startWebSpeechReading(content);
        } else {
            // Fallback: Sử dụng API TTS bên ngoài
            await startExternalTTSReading(content);
        }
        
        voiceBtn.classList.add('playing');
        voiceBtnText.innerHTML = '<i class="fa-solid fa-stop"></i> Dừng đọc';
        speedControl.style.display = 'flex';
        const voiceSelectControl = document.getElementById('voiceSelectControl');
        if (voiceSelectControl) {
            voiceSelectControl.style.display = 'flex';
        }
        isPlaying = true;
    }
}

// Sử dụng Web Speech API (hỗ trợ tiếng Việt)
function startWebSpeechReading(text) {
    if (currentUtterance) {
        speechSynthesis.cancel();
    }
    
    // Chia nhỏ văn bản thành các đoạn để đọc
    const sentences = text.split(/[.!?。！？]\s+/).filter(s => s.trim().length > 0);
    let currentIndex = 0;
    
    function speakNext() {
        if (currentIndex >= sentences.length) {
            isPlaying = false;
            document.getElementById('voiceBtn').classList.remove('playing');
            document.getElementById('voiceBtnText').innerHTML = '<i class="fa-solid fa-volume-high"></i> Đọc bằng giọng AI';
            document.getElementById('voiceSpeedControl').style.display = 'none';
            const voiceSelectControl = document.getElementById('voiceSelectControl');
            if (voiceSelectControl) {
                voiceSelectControl.style.display = 'none';
            }
            return;
        }
        
        const utterance = new SpeechSynthesisUtterance(sentences[currentIndex]);
        
        // Sử dụng giọng đã chọn hoặc tìm giọng tiếng Việt tốt nhất
        if (selectedVoice) {
            utterance.voice = selectedVoice;
            utterance.lang = selectedVoice.lang;
        } else {
            // Fallback: tìm giọng tiếng Việt
            const voices = speechSynthesis.getVoices();
            const vietnameseVoice = voices.find(voice => 
                voice.lang.includes('vi') || voice.lang.includes('VN') ||
                voice.name.toLowerCase().includes('vietnamese')
            ) || voices.find(voice => voice.lang.includes('en'));
            
            if (vietnameseVoice) {
                utterance.voice = vietnameseVoice;
                utterance.lang = vietnameseVoice.lang;
            } else {
                utterance.lang = 'vi-VN';
            }
        }
        utterance.rate = currentSpeed;
        utterance.pitch = 1;
        utterance.volume = 1;
        
        utterance.onend = function() {
            currentIndex++;
            speakNext();
        };
        
        utterance.onerror = function(event) {
            console.error('Speech synthesis error:', event);
            isPlaying = false;
            document.getElementById('voiceBtn').classList.remove('playing');
            document.getElementById('voiceBtnText').innerHTML = '<i class="fa-solid fa-volume-high"></i> Đọc bằng giọng AI';
            const voiceSelectControl = document.getElementById('voiceSelectControl');
            if (voiceSelectControl) {
                voiceSelectControl.style.display = 'none';
            }
            alert('Có lỗi xảy ra khi đọc. Vui lòng thử lại.');
        };
        
        currentUtterance = utterance;
        speechSynthesis.speak(utterance);
        currentIndex++;
    }
    
    // Đợi voices load xong
    if (speechSynthesis.getVoices().length === 0) {
        speechSynthesis.onvoiceschanged = function() {
            speakNext();
        };
    } else {
        speakNext();
    }
}

// Sử dụng API TTS bên ngoài (nếu có)
async function startExternalTTSReading(text) {
    try {
        // Chia nhỏ văn bản thành các đoạn (mỗi đoạn tối đa 5000 ký tự)
        const maxLength = 5000;
        const chunks = [];
        for (let i = 0; i < text.length; i += maxLength) {
            chunks.push(text.substring(i, i + maxLength));
        }
        
        // Đọc từng đoạn
        for (const chunk of chunks) {
            // Sử dụng API TTS với key được cung cấp
            // Lưu ý: Cần điều chỉnh URL API dựa trên dịch vụ TTS thực tế
            const response = await fetch('https://api.example.com/tts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${TTS_API_KEY}`
                },
                body: JSON.stringify({
                    text: chunk,
                    language: 'vi',
                    speed: currentSpeed
                })
            });
            
            if (response.ok) {
                const audioBlob = await response.blob();
                const audioUrl = URL.createObjectURL(audioBlob);
                const audio = new Audio(audioUrl);
                
                await new Promise((resolve, reject) => {
                    audio.onended = resolve;
                    audio.onerror = reject;
                    audio.play();
                });
                
                URL.revokeObjectURL(audioUrl);
            } else {
                // Fallback về Web Speech API
                if (initSpeechSynthesis()) {
                    startWebSpeechReading(chunk);
                    break;
                }
            }
        }
    } catch (error) {
        console.error('TTS API Error:', error);
        // Fallback về Web Speech API
        if (initSpeechSynthesis()) {
            startWebSpeechReading(text);
        } else {
            alert('Không thể kết nối với dịch vụ đọc sách. Vui lòng thử lại sau.');
        }
    }
}

function stopVoiceReading() {
    if (speechSynthesis && speechSynthesis.speaking) {
        speechSynthesis.cancel();
    }
    if (currentUtterance) {
        currentUtterance = null;
    }
}

function updateVoiceSpeed() {
    const speedSlider = document.getElementById('voiceSpeed');
    const speedValue = document.getElementById('speedValue');
    currentSpeed = parseFloat(speedSlider.value);
    speedValue.textContent = currentSpeed.toFixed(1) + 'x';
    
    if (isPlaying && currentUtterance) {
        currentUtterance.rate = currentSpeed;
    }
}

// Khởi tạo khi trang load
document.addEventListener('DOMContentLoaded', function() {
    initSpeechSynthesis();
    
    // Tải nội dung từ EPUB
    loadEpubContent();
    
    // Dừng đọc khi rời trang
    window.addEventListener('beforeunload', function() {
        stopVoiceReading();
    });
});

// Tải nội dung từ file EPUB
async function loadEpubContent() {
    const bookContent = document.getElementById('bookContent');
    const productId = new URLSearchParams(window.location.search).get('id');
    
    if (!productId) {
        bookContent.innerHTML = '<p style="color: #dc3545;">Mã sách không hợp lệ.</p>';
        return;
    }
    
    try {
        const response = await fetch('${baseURL}/read-epub?id=' + encodeURIComponent(productId));
        
        if (!response.ok) {
            // Nếu response không OK, thử parse JSON để lấy message
            try {
                const errorData = await response.json();
                bookContent.innerHTML = '<p style="color: #dc3545; text-align: center; padding: 2rem;">' + 
                    escapeHtml(errorData.message || 'Không thể tải nội dung sách.') + '</p>';
            } catch (e) {
                bookContent.innerHTML = '<p style="color: #dc3545; text-align: center; padding: 2rem;">' + 
                    'Không tìm thấy file nội dung sách. (HTTP ' + response.status + ')</p>';
            }
            return;
        }
        
        const data = await response.json();
        
        if (data.success && data.content) {
            // Hiển thị nội dung
            bookContent.innerHTML = '<div style="white-space: pre-wrap; line-height: 1.8;">' + 
                escapeHtml(data.content) + '</div>';
        } else {
            bookContent.innerHTML = '<p style="color: #dc3545; text-align: center; padding: 2rem;">' + 
                escapeHtml(data.message || 'Không thể tải nội dung sách. Vui lòng thử lại sau.') + '</p>';
        }
    } catch (error) {
        console.error('Lỗi khi tải nội dung EPUB:', error);
        bookContent.innerHTML = '<p style="color: #dc3545; text-align: center; padding: 2rem;">' + 
            'Đã xảy ra lỗi khi tải nội dung sách. Vui lòng thử lại sau.<br><small>' + 
            escapeHtml(error.message || '') + '</small></p>';
    }
}

// Escape HTML để tránh XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<jsp:include page="layout/footer.jsp" />

